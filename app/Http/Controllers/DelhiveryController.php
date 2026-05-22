<?php
 
namespace App\Http\Controllers;
 
use App\Services\DelhiveryService;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\ShiprocketService;

class DelhiveryController extends Controller
{
    protected DelhiveryService $delhivery;
 
    public function __construct(DelhiveryService $delhivery)
    {
        $this->delhivery = $delhivery;
    }
 
    public function getPincode(Request $request) 
    {
        $pincode = $request->input('pincode', '388260'); // Default if not provided
        $paymentMethod = $request->input('paymentMethod', 'cod');
        $isCod = 1;
        if(isset($paymentMethod) && $paymentMethod != 'cod'){
            $isCod = 0;
        }
        //try {
            $client = new \GuzzleHttp\Client(); 
 
            //OLD CODE
            // Construct full URL exactly as in Delhivery docs 
            // $url = "https://track.delhivery.com/c/api/pin-codes/json/?filter_codes={$pincode}";
            // $response = $client->request('GET', $url, [
            //     'headers' => [
            //         'Authorization' => 'Token ' . env('DELHIVERY_API_TOKEN'),
            //         'Accept' => 'application/json',
            //     ],
            //     'http_errors' => false, // prevent Guzzle from throwing on 4xx
            // ]);

            // NEW CODE
            $shiprocketService = new ShiprocketService(new Client());
            return $shiprocketService->checkServiceability($pincode, $isCod);
            
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'error' => $e->getMessage()
        //     ], 500);
        // }
    }
    
    public function getShippingCharges(Request $request)
    {
        $pincode       = $request->input('pincode', '388260'); 
        $weight        = $request->input('weight', 500);
        $paymentMethod = $request->input('payment_method', 'Prepaid'); // COD OR Prepaid

        try {

            $response = Http::withHeaders([
                'Authorization' => 'Token ' . env('DELHIVERY_API_TOKEN'),
                'Content-Type'  => 'application/json',
            ])->get(env('DELHIVERY_API_URL'). 'kinko/v1/invoice/charges/.json', [

                'md'    => 'E',
                'ss'    => 'Delivered', // after changes to Created
                'd_pin' => $pincode,
                'o_pin' => '380015', // sender pickup pincode (can make dynamic)
                'cgm'   => $weight,
                'pt'    => ($paymentMethod == 'cod') ? 'COD' : 'Prepaid'

            ]);
            $statusCode = $response->getStatusCode();
            $charges = $response->json();

            return response()->json([
                'status'  => 'success',
                'code'   => $statusCode,
                'charges' => $charges
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);

        }
    }

    public function createShipment(Request $request)
    {
        // Build the payload
        $payload = [
            'shipments' => [
                [
                    'name'           => $request->input('name'),
                    'add'            => $request->input('address'),
                    'pin'            => $request->input('pincode'),
                    // 'city'           => $request->input('city'),
                    // 'state'          => $request->input('state'),
                    // 'country'        => $request->input('country'),
                    'phone'          => $request->input('phone'),
                    'order'          => $request->input('order_number'),
                    'payment_mode'   => $request->input('payment_method'), // "COD" or "Prepaid"
                    'products_desc'  => $request->input('products_desc'),
                    'total_amount'   => $request->input('total_amount'),
                    'cod_amount'     => $request->input('cod_amount') ?: '0',
                    'weight'         => $request->input('weight'),
                    'shipment_width' => $request->input('width'),
                    'shipment_height'=> $request->input('height'),
                    'shipping_mode'  => $request->input('shipping_mode', 'Surface'),
                    // add other keys if needed...
                ] 
            ],
            'pickup_location' => [
                'name' => 'PRADASHIMART SURFACE'  // Must match Delhivery account
            ]
        ];

        // Make the request
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . env('DELHIVERY_API_TOKEN_TEST'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ])->asForm()->post('https://staging-express.delhivery.com/api/cmu/create.json', [
            'format' => 'json',
            'data'   => json_encode($payload)
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['packages'])) {

                return response()->json([
                    'status' => 'success',
                    'data'   => $data 
                ]);
        
        } else {
            return response()->json([
                'status'  => 'error',
                'message' => $data['message'] ?? 'Failed to create shipment',
                'data'    => $data
            ], 500);
        }
    }


    public function labelGeneration(Request $request)
    {
        $waybillNumbers = $request->input('waybillNumbers'); // array of waybills
        $url = 'https://staging-express.delhivery.com/api/p/packing_slip';

        // Build query manually (do not encode comma)
        $queryString = 'wbns=' . implode(',', $waybillNumbers) . '&pdf=true&pdf_size=4R';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . env('DELHIVERY_API_TOKEN_TEST'),
                'Content-Type' => 'application/json',
                'User-Agent' => 'PostmanRuntime/7.31.0', // mimic Postman
            ])->get($url . '?' . $queryString);
            // dd($response);
            if ($response->successful()) {
                // return $response->body();
                 $data = $response->json(); // convert string → array

                // Get single link
                $pdfLink = $data['packages'][0]['pdf_download_link'];


                return response()->json([
                    'status' => 'success',
                    'message' => 'Label generated successfully',
                    'pdflink' => $pdfLink
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate label',
                'response' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }





    public function schedulePickup($waybill)
    {
        $url = "https://staging-express.delhivery.com/api/cmu/pickup";

        $payload = [
            "pickup_location" => "Your Warehouse Code",
            "waybill" => $waybill,
            "expected_package_count" => 1,
            "pickup_date" => now()->format('Y-m-d') // optional
        ];

        $response = Http::withHeaders([
            "Authorization" => "Token " . env("DELHIVERY_API_TOKEN_TEST"),
            "Content-Type" => "application/json"
        ])->post($url, $payload);

        return $response->json();
    }
  
} 