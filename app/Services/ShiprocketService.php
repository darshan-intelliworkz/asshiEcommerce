<?php
 
namespace App\Services;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderReturnRequest;
use App\Models\ShipmentDetails;

class ShiprocketService
{
    protected $client;
    protected $url;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->url = env('SHIPROCKET_API_URL');
    }

    // Helper function to fetch the token (cached for 24 hours)
    public function getAuthToken()
    {
        // Check if token exists in cache and is still valid
        $token = Cache::get('shiprocket_token');

        if (!$token) {
            // If token is not cached or expired, generate a new token
            $body = json_encode([
                'email' => env('SHIPROCKET_EMAIL'),
                'password' => env('SHIPROCKET_PASSWORD'),
            ]);

            try {
                $response = $this->client->post($this->url.'/auth/login', [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => $body,
                ]);
                $responseBody = $response->getBody()->getContents();
                $data = json_decode($responseBody, true);
                if (isset($data['token']) && !empty($data['token'])) {
                    // Store token in cache for 24 hours
                    Cache::put('shiprocket_token', $data['token'], now()->addHours(24));
                    return $data['token'];
                } else {
                    throw new \Exception('Token generation failed');
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                // Handle API errors gracefully
                throw new \Exception('Failed to fetch token: ' . $e->getMessage());
            }
        }

        return $token;
    }

    // Function to check the serviceability
    public function checkServiceability($pincode, $isCod)
    {
        try {
            // Get the auth token
            $authToken = $this->getAuthToken();

            // Make the API request to check serviceability
            $response = $this->client->get($this->url.'/courier/serviceability/', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $authToken,
                ],
                'query' => [
                    'pickup_postcode' => env('SHIPROCKET_PICKUP_PINCODE'),
                    'delivery_postcode' => $pincode,
                    'cod' => $isCod,
                    'weight' => env('SHIPROCKET_PICKUP_WEIGHT'),
                ],
            ]);

            $body = $response->getBody()->getContents();
            $data = json_decode($body);

            if (json_last_error() === JSON_ERROR_NONE && $response->getStatusCode() == 200) {
                return response()->json([
                    'status' => $response->getStatusCode(),
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'status' => $response->getStatusCode(),
                    'raw_response' => $data
                ]);
            }

        } catch (\Exception $e) {
            // Catch token-related or other errors
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createShipmentOrder($order)
    {
        try {
            $authToken = $this->getAuthToken();
            // Fetch order products
            $orderItems = $order->cart_info;
            $items = [];
            foreach ($orderItems as $item) {
                $size = null;
                if (!empty($item->size_price)) {
                    $sizeData = json_decode($item->size_price, true);
                    $size = $sizeData['size'] ?? null;
                }
                
                $colorName = null;
                $productName = $item->product->product_code ?? 'Product';
                $sku = $productName.'_'.$item->product_id;
                if(isset($item->color_id) && $item->color_id != null){
                    $colorName = optional($item->color)->color_name;
                    $sku .= '_' . $colorName;
                    if(isset($colorName) && $colorName != null){
                        $productName .= ' | Color:'.$colorName.')';
                    }
                }
                if($size != null){
                    $sku .= '_' . $size;
                    $productName .= ' | Size:'.$size;
                }else{
                    $productName .= ' | Size: N/A';
                }

                $items[] = [
                    'name' => $productName,
                    'sku' => $sku,
                    'units' => $item->quantity,
                    'selling_price' => $item->price + $item->gst_amt,
                    'discount' => 0,
                    'tax' => $item->gst_percent ?? 0,
                ];
            }
            $pTotal = round($order->sub_total + $order->total_gst_amount);
            $payload = [
                'order_id' => $order->order_number,
                'order_date' => $order->created_at->format('Y-m-d H:i'),
                'pickup_location' => 'work',
                'billing_customer_name' => $order->first_name,
                'billing_last_name' => $order->last_name,
                'billing_address' => $order->address1,
                'billing_address_2' => $order->address2,
                'billing_city' => $order->city ?? 'Delhi',
                'billing_pincode' => $order->post_code,
                'billing_state' => $order->state ?? 'Delhi',
                'billing_country' => $order->country,
                'billing_email' => $order->email,
                'billing_phone' => $order->phone,
                'shipping_is_billing' => true,
                'order_items' => $items,
                'payment_method' => $order->payment_method === 'cod' ? 'COD' : 'Prepaid',
                'shipping_charges' => (int) $order->shiping_charges,
                'giftwrap_charges' => 0,
                'transaction_charges' => 0,
                'total_discount' => (int) $order->coupon,
                'sub_total' => (int)$pTotal,
                //'sub_total' => isset($order->coupon) && $order->coupon != NULL ? (int) $order->sub_total - (int) $order->coupon : (int) $order->sub_total,
                // Dummy dimensions (required)
                'length' => 10,
                'breadth' => 15,
                'height' => 20,
                'weight' => 0.5
            ];
            $response = $this->client->post($this->url.'/orders/create/adhoc',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $authToken,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => $payload
                ]
            );
            $data = json_decode($response->getBody()->getContents(), true);
            Log::channel('shiprocket')->info("Shipment Order Created", $data);
            return response()->json([
                'status' => true,
                'shiprocket_response' => $data
            ]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            return response()->json([
                'status' => false,
                'error' => $e->getResponse()
                    ? json_decode($e->getResponse()->getBody()->getContents(), true)
                    : $e->getMessage()
            ], 500);
        } 
    }

    public function createCompleteShipmentForOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $shipment = ShipmentDetails::where('order_id', $order->id)->lockForUpdate()->first();
            if (!$shipment) {
                $shipment = new ShipmentDetails(['order_id' => $order->id]);
            }
            $shipment->order_number = $order->order_number;
            if (!$shipment->exists) {
                $shipment->save();
            }

            if (empty($shipment->shipment_id) || empty($shipment->shipment_order_id)) {
                $orderResponse = $this->createShipmentOrder($order);
                $orderResponseData = $orderResponse->getData(true);

                Log::info('Order Shipment Response: ', $orderResponseData);

                $shiprocketResponse = $orderResponseData['shiprocket_response'] ?? [];
                $isCreated = !empty($orderResponseData['status'])
                    && strtolower($shiprocketResponse['status'] ?? '') === 'new'
                    && (int) ($shiprocketResponse['status_code'] ?? 0) === 1;

                if (!$isCreated) {
                    $shipment->shipment_response = json_encode($orderResponseData);
                    $shipment->save();

                    return response()->json([
                        'status' => false,
                        'message' => 'Shiprocket order could not be created.',
                        'shiprocket_response' => $orderResponseData,
                    ]);
                }

                $shipment->shipment_status = 'New';
                $shipment->shipment_id = $shiprocketResponse['shipment_id'] ?? null;
                $shipment->shipment_order_id = $shiprocketResponse['order_id'] ?? null;
                $shipment->shipment_response = json_encode($orderResponseData);
                $shipment->save();
            }

            if (empty($shipment->shipment_awb)) {
                $orderAwbResponse = $this->assignCourierAwb($shipment->shipment_id);
                $orderAwbResponseData = $orderAwbResponse->getData(true);

                Log::info('Order AWB Response: ', $orderAwbResponseData);

                if (
                    !empty($orderAwbResponseData['status'])
                    && (int) ($orderAwbResponseData['shiprocket_response']['awb_assign_status'] ?? 0) === 1
                ) {
                    $awbData = $orderAwbResponseData['shiprocket_response']['response']['data'] ?? [];
                    $shipment->shipment_awb = $awbData['awb_code'] ?? '';
                    $shipment->save();
                }
            }

            if (!empty($shipment->shipment_awb) && empty($shipment->label_pdf)) {
                $orderLabelGenerateResponse = $this->generateLabel($shipment->shipment_id);
                $orderLabelGenerateResponseData = $orderLabelGenerateResponse->getData(true);

                Log::info('Order Label Generate Response: ', $orderLabelGenerateResponseData);

                if (
                    (int) ($orderLabelGenerateResponseData['shiprocket_response']['label_created'] ?? 0) === 1
                    && !empty($orderLabelGenerateResponseData['shiprocket_response']['label_url'])
                ) {
                    $shipment->label_pdf = $orderLabelGenerateResponseData['shiprocket_response']['label_url'];
                    $shipment->save();
                }
            }

            if (!empty($shipment->label_pdf) && empty($shipment->pickup_request_response)) {
                $orderShipmentPickupResponse = $this->shipmentPickupRequest($shipment->shipment_id);
                $orderShipmentPickupResponseData = $orderShipmentPickupResponse->getData(true);

                Log::info('Order Shipment Pickup Response: ', $orderShipmentPickupResponseData);

                if ((int) ($orderShipmentPickupResponseData['shiprocket_response']['pickup_status'] ?? 0) === 1) {
                    $shipment->pickup_request_response = json_encode($orderShipmentPickupResponseData);
                    $shipment->scheduled_at = $orderShipmentPickupResponseData['shiprocket_response']['response']['pickup_scheduled_date'] ?? null;
                    $shipment->save();
                }
            }

            if (!empty($shipment->label_pdf) && empty($shipment->manifest_url)) {
                $orderGenerateMenifeastResponse = $this->generateManifeast($shipment->shipment_id);
                $orderGenerateMenifeastResponseData = $orderGenerateMenifeastResponse->getData(true);

                Log::info('Order Generate Manifest Response: ', $orderGenerateMenifeastResponseData);

                if (
                    !empty($orderGenerateMenifeastResponseData['status'])
                    && (int) ($orderGenerateMenifeastResponseData['shiprocket_response']['status'] ?? 0) === 1
                    && !empty($orderGenerateMenifeastResponseData['shiprocket_response']['manifest_url'])
                ) {
                    $shipment->manifest_url = $orderGenerateMenifeastResponseData['shiprocket_response']['manifest_url'];
                    $shipment->shipment_status = 'Pickup Scheduled';
                    $shipment->save();
                }
            }

            if (!empty($shipment->shipment_order_id) && empty($shipment->invoice_url)) {
                $orderGenerateInvoiceResponse = $this->generateInvoice($shipment->shipment_order_id);
                $orderGenerateInvoiceResponseData = $orderGenerateInvoiceResponse->getData(true);

                Log::info('Order Generate Invoice Response: ', $orderGenerateInvoiceResponseData);

                if (
                    !empty($orderGenerateInvoiceResponseData['status'])
                    && !empty($orderGenerateInvoiceResponseData['shiprocket_response']['invoice_url'])
                ) {
                    $shipment->invoice_url = $orderGenerateInvoiceResponseData['shiprocket_response']['invoice_url'];
                    $shipment->save();
                }
            }

            if (!empty($shipment->shipment_order_id)) {
                $order->status = 'process';
                $order->save();
            }

            return response()->json([
                'status' => true,
                'shipment' => $shipment->fresh(),
            ]);
        });
    }

    public function assignCourierAwb($shipmentId){
        if(isset($shipmentId) && $shipmentId != NULL){
            try {
                $authToken = $this->getAuthToken();
                $response = $this->client->post($this->url.'/courier/assign/awb', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $authToken,
                    ],
                    'json' => [
                        'shipment_id' => $shipmentId
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                Log::channel('shiprocket')->info("AWB Assigned", $data);
                return response()->json([
                    'status' => true,
                    'shiprocket_response' => $data
                ]);
            } catch (\Exception $e) {
                Log::channel('shiprocket')->error("Assign AWB Error: " . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        }else{
            return response()->json([
                'status' => false,
                'shiprocket_response' => '',
            ], 500);
        }
    }

    public function generateLabel($shipmentId){
        if(isset($shipmentId) && $shipmentId != NULL){
            try {
                $authToken = $this->getAuthToken();
                $response = $this->client->post($this->url.'/courier/generate/label', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $authToken,
                    ],
                    'json' => [
                        'shipment_id' => [(int) $shipmentId]
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                return response()->json([
                    'status' => true,
                    'shiprocket_response' => $data
                ]);
            } catch (\Exception $e) {
                \Log::error("Generate Label Error: " . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        }else{
            return response()->json([
                'status' => false,
                'shiprocket_response' => '',
            ], 500);
        }
    } 

    public function shipmentPickupRequest($shipmentId){
        if(isset($shipmentId) && $shipmentId != NULL){
            try {
                $authToken = $this->getAuthToken();
                $response = $this->client->post($this->url.'/courier/generate/pickup', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $authToken,
                    ],
                    'json' => [
                        'shipment_id' => [(int) $shipmentId]
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                    Log::channel('shiprocket')->info("Shipment Pickup Request", $data);
                return response()->json([
                    'status' => true,
                    'shiprocket_response' => $data
                ]);
            } catch (\Exception $e) {
                Log::channel('shiprocket')->error("Shipment Pickup Request Error: " . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        }else{
            return response()->json([
                'status' => false,
                'shiprocket_response' => '',
            ], 500);
        }
    }

    public function generateManifeast($shipmentId){
        if(isset($shipmentId) && $shipmentId != NULL){
            try {
                $authToken = $this->getAuthToken();
                $response = $this->client->post($this->url.'/manifests/generate', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $authToken,
                    ],
                    'json' => [
                        'shipment_id' => [(int) $shipmentId]
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                return response()->json([
                    'status' => true,
                    'shiprocket_response' => $data
                ]);
            } catch (\Exception $e) {
                \Log::error("Menifeast Generate Error: " . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        }else{
            return response()->json([
                'status' => false,
                'shiprocket_response' => '',
            ], 500);
        }
    }
    
    public function generateInvoice($orderIds){
        if(isset($orderIds) && $orderIds != NULL){
            try {
                $authToken = $this->getAuthToken();
                $response = $this->client->post($this->url.'/orders/print/invoice', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $authToken,
                    ],
                    'json' => [
                        'ids' => [(int) $orderIds]
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                return response()->json([
                    'status' => true,
                    'shiprocket_response' => $data
                ]);
            } catch (\Exception $e) {
                \Log::error("Invoice Generate Error: " . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        }else{
            return response()->json([
                'status' => false,
                'shiprocket_response' => '',
            ], 500);
        }
    }

    public function getShipmentStatus($shipmentId)
    {
        try {
            $authToken = $this->getAuthToken();

            $response = $this->client->get($this->url.'/shipments/'.$shipmentId, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $authToken,
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return response()->json([
                'status' => true,
                'shiprocket_response' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error("SHIPMENT STATUS ERROR: " . $e->getMessage());

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }


    public function createReturnOrder($orderId, OrderReturnRequest $returnRequest = null)
    {
        try {
            $order = Order::with([
                'cart_info.product',
                'cart_info.color'
            ])->findOrFail($orderId);

            $authToken = $this->getAuthToken();

            $items = [];

            $cartItems = $returnRequest && $returnRequest->cart_id
                ? $order->cart_info->where('id', $returnRequest->cart_id)
                : $order->cart_info;

            foreach ($cartItems as $item) {

                $size = null;

                if (!empty($item->size_price)) {
                    $sizeData = json_decode($item->size_price, true);
                    $size = $sizeData['size'] ?? null;
                }

                $colorName = optional($item->color)->color_name;

                $productName = $item->product->title ?? 'Product';

                if ($colorName) {
                    $productName .= ' | Color: ' . $colorName;
                }

                if ($size) {
                    $productName .= ' | Size: ' . $size;
                }

                $items[] = [
                    "name" => $productName,
                    "sku" => 'RETURN_' . $item->product_id,
                    "units" => $item->quantity,
                    "selling_price" => $item->price,
                    "discount" => 0,
                    "tax" => $item->gst_percent ?? 0,
                ];
            }


            $payload = [
                "order_id" => 'RETURN_' . $order->order_number . ($returnRequest ? '_' . $returnRequest->id : ''),
                "order_date" => now()->format('Y-m-d H:i'),

                // PICKUP FROM CUSTOMER
                "pickup_customer_name" => $order->first_name . ' ' . $order->last_name,
                "pickup_address" => $order->address1,
                "pickup_address_2" => $order->address2,
                "pickup_city" => $order->city,
                "pickup_state" => $order->state,
                "pickup_country" => $order->country ?? 'India',
                "pickup_pincode" => $order->post_code,
                "pickup_email" => $order->email,
                "pickup_phone" => $order->phone,

                // YOUR WAREHOUSE
                "shipping_customer_name" => env('SHIPROCKET_STORE_NAME'),
                "shipping_address" => env('SHIPROCKET_STORE_ADDRESS'),
                "shipping_city" => env('SHIPROCKET_STORE_CITY'),
                "shipping_state" => env('SHIPROCKET_STORE_STATE'),
                "shipping_country" => "India",
                "shipping_pincode" => env('SHIPROCKET_STORE_PINCODE'),
                "shipping_email" => env('SHIPROCKET_STORE_EMAIL'),
                "shipping_phone" => env('SHIPROCKET_STORE_PHONE'),

                // ITEMS
                "order_items" => $items,

                "payment_method" => "Prepaid",
                "sub_total" => $cartItems->sum('amount') ?: $cartItems->sum('price'),

                "reason" => $returnRequest->reason ?? 'Arrived too late',

                // DIMENSIONS
                "length" => 10,
                "breadth" => 10,
                "height" => 10,
                "weight" => 0.5
            ];

            Log::info('RETURN PAYLOAD', $payload);

            /*
            |--------------------------------------------------------------------------
            | API CALL
            |--------------------------------------------------------------------------
            */

            $response = $this->client->post(
                $this->url . '/orders/create/return',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $authToken,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => $payload
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);

            Log::info('RETURN RESPONSE', $data);

            return response()->json([
                'status' => true,
                'shiprocket_response' => $data
            ]);

        } catch (\Exception $e) {

            Log::error('RETURN ORDER ERROR: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function assignReturnCourierAWB($shipmentId)
    {
        try {

            $authToken = $this->getAuthToken();

            $response = $this->client->post(
                $this->url . '/courier/assign/awb',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $authToken,
                    ],
                    'json' => [
                        'shipment_id' => $shipmentId
                    ]
                ]
            );

            $data = json_decode(
                $response->getBody()->getContents(),
                true
            );

            Log::info('RETURN AWB RESPONSE', $data);

            if (
                isset($data['awb_assign_status']) &&
                $data['awb_assign_status'] == 1
            ) {

                return response()->json([
                    'status' => true,
                    'message' => 'AWB assigned successfully',
                    'awb_number' => $data['response']['awb_code'] ?? null,
                    'courier_name' => $data['response']['courier_name'] ?? null,
                    'shipment_id' => $shipmentId,
                    'shiprocket_response' => $data
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'AWB assignment failed',
                'shiprocket_response' => $data
            ]);

        } catch (\Exception $e) {

            Log::error('RETURN AWB ERROR: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function trackReturnShipment($awbCode)
    {
        try {

            $authToken = $this->getAuthToken();

            $response = $this->client->get(

                $this->url . '/courier/track/awb/' . $awbCode,

                [
                    'headers' => [
                        'Authorization' =>
                            'Bearer ' . $authToken,

                        'Content-Type' =>
                            'application/json',
                    ]
                ]
            );

            $data = json_decode(
                $response->getBody()->getContents(),
                true
            );

            Log::info(
                'RETURN TRACKING RESPONSE',
                $data
            );

            return response()->json([
                'status' => true,
                'shiprocket_response' => $data
            ]);

        } catch (\Exception $e) {

            Log::error(
                'RETURN TRACKING ERROR: ' .
                $e->getMessage()
            );

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createCancelOrder($orderId){
        if(isset($orderId) && $orderId != NULL){
            try {
                $authToken = $this->getAuthToken();
                $response = $this->client->post($this->url.'/orders/cancel', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $authToken,
                    ],
                    'json' => [
                        'ids' => [(int)$orderId]
                    ]
                ]
            );

            $data = json_decode(
                $response->getBody()->getContents(),
                true
            );
            Log::channel('shiprocket')->info(
                'Cancel Shipment Order Response',
                $data
            );
            return response()->json([
                'status' => true,
                'shiprocket_response' => $data
            ]);

            } catch (\Exception $e) {
                Log::channel('shiprocket')->error(
                    'Order Cancel Error: '.$e->getMessage(),
                );

                throw $e;
            }
        }
    }

    // order cancel request to shiprocket OLD
    // public function cancelShipmentOrder($shiprocketOrderId)
    // {
    //     try {
    //         Log::channel('shiprocket')->info(
    //             'Cancel Shipment Order Request',
    //             ['shiprocket_order_id' => $shiprocketOrderId]
    //         );
    //         $authToken = $this->getAuthToken();

    //         $response = $this->client->post(
    //             $this->url . '/orders/cancel',
    //             [
    //                 'headers' => [
    //                     'Content-Type' => 'application/json',
    //                     'Authorization' => 'Bearer ' . $authToken,
    //                 ],
    //                 'json' => [
    //                     'ids' => [$shiprocketOrderId]
    //                 ]
    //             ]
    //         );

    //         $data = json_decode(
    //             $response->getBody()->getContents(),
    //             true
    //         );
    //         Log::channel('shiprocket')->info(
    //             'Cancel Shipment Order Response',
    //             $data
    //         );
    //         return response()->json([
    //             'status' => true,
    //             'shiprocket_response' => $data
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::channel('shiprocket')->error(
    //             'Order Cancel Error: '.$e->getMessage(),
    //         );

    //         throw $e;
    //     }
    // }

    // order cancel request to shiprocket NEW
    public function cancelShipmentOrder($returnRequest)
    {
        try {
            $authToken = $this->getAuthToken();
            $endpoint = null;
            $payload = [];

            if ($returnRequest->awb_code) {
                // Cancel return/exchange shipment
                $endpoint = '/orders/cancel/shipment/awbs';
                $payload = [
                    'awbs' => [$returnRequest->awb_code]
                ];

            } elseif ($returnRequest->return_type === 'exchange' && $returnRequest->exchange_order_id) {

                // Cancel exchange order
                $endpoint = '/orders/cancel';
                $payload = [
                    'ids' => [$returnRequest->exchange_order_id]
                ];

            } elseif ($returnRequest->shiprocket_return_order_id) {

                // Cancel return order
                $endpoint = '/orders/cancel';
                $payload = [
                    'ids' => [$returnRequest->shiprocket_return_order_id]
                ];

            } else {
                return [
                    'status' => true,
                    'message' => 'No Shiprocket cancellation required'
                ];
            }
            $response = $this->client->post(
                $this->url . $endpoint,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $authToken,
                    ],
                    'json' => $payload
                ]
            );
            $data = json_decode(
                $response->getBody()->getContents(),
                true
            );

            return response()->json([
                'status' => true,
                'shiprocket_response' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::channel('shiprocket')->error(
                'Cancel Return Error: '.$e->getMessage()
            );

            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    public function createExchangeOrder($returnRequest)
    {
        try {
            $returnRequest = OrderReturnRequest::with([
                'order.cart_info.product',
                'order.cart_info.color',
                'cart.product',
                'cart.color',
            ])->findOrFail($returnRequest->id);

            if ($returnRequest->return_type !== 'exchange') {
                return response()->json([
                    'status' => false,
                    'message' => 'This request is not an exchange request.',
                ], 422);
            }

            if ($returnRequest->exchange_order_id || $returnRequest->exchange_shipment_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Shiprocket exchange order already exists for this request.',
                ], 422);
            }

            $originalOrder = $returnRequest->order;

            if (!$originalOrder) {
                return response()->json([
                    'status' => false,
                    'message' => 'Original order not found.',
                ], 404);
            }

            $cartItems = $returnRequest->cart_id
                ? $originalOrder->cart_info->where('id', $returnRequest->cart_id)
                : $originalOrder->cart_info;

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No exchange product found for this request.',
                ], 422);
            }

            $sellerPickupLocationId = env('SHIPROCKET_SELLER_PICKUP_LOCATION_ID');
            $sellerShippingLocationId = env('SHIPROCKET_SELLER_SHIPPING_LOCATION_ID', $sellerPickupLocationId);
            $returnReasonId = (int) env('SHIPROCKET_EXCHANGE_RETURN_REASON_ID', 29);
            $channelId = env('SHIPROCKET_CHANNEL_ID');

            if (empty($sellerPickupLocationId) || empty($sellerShippingLocationId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Shiprocket seller pickup/shipping location IDs are not configured. Please set SHIPROCKET_SELLER_PICKUP_LOCATION_ID and SHIPROCKET_SELLER_SHIPPING_LOCATION_ID in .env.',
                ], 422);
            }

            if (!in_array($returnReasonId, [29, 30, 31, 32, 33, 34, 28, 27, 35, 36, 26, 25], true)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Shiprocket exchange return reason ID. Use one of: 29, 30, 31, 32, 33, 34, 28, 27, 35, 36, 26, 25.',
                ], 422);
            }

            $items = [];

            foreach ($cartItems as $item) {
                $size = null;

                if (!empty($item->size_price)) {
                    $sizeData = json_decode($item->size_price, true);
                    $size = $sizeData['size'] ?? null;
                }

                $colorName = optional($item->color)->color_name;
                $productName = $item->product->title ?? 'Product';

                if ($colorName) {
                    $productName .= ' | Color: ' . $colorName;
                }

                if ($size) {
                    $productName .= ' | Size: ' . $size;
                }

                $sku = 'EXCHANGE_' . $item->product_id . ($size ? '_' . $size : '');

                $items[] = [
                    'name' => $productName,
                    'sku' => $sku,
                    'units' => $item->quantity,
                    'selling_price' => $item->price + $item->gst_amt,
                    'discount' => 0,
                    'tax' => $item->gst_percent ?? 0,
                    'exchange_item_sku' => $sku,
                    'exchange_item_name' => $productName,
                ];
            }

            $subTotal = $cartItems->sum(function ($item) {
                return ($item->price + $item->gst_amt) * $item->quantity;
            });

            $paymentMethod = strtolower((string) $originalOrder->payment_method) === 'cod'
                ? 'COD'
                : 'Prepaid';

            $payload = [
                'exchange_order_id' => 'EXCHANGE_' . $originalOrder->order_number . '_' . $returnRequest->id,
                'return_order_id' => 'RETURN_' . $originalOrder->order_number . '_' . $returnRequest->id,
                'order_date' => now()->format('Y-m-d H:i'),
                'seller_pickup_location_id' => $sellerPickupLocationId,
                'seller_shipping_location_id' => $sellerShippingLocationId,

                'buyer_shipping_first_name' => $originalOrder->first_name ?? '',
                'buyer_shipping_last_name' => $originalOrder->last_name ?? '',
                'buyer_shipping_address' => $originalOrder->address1 ?? '',
                'buyer_shipping_address_2' => $originalOrder->address2 ?? '',
                'buyer_shipping_city' => $originalOrder->city ?? 'Delhi',
                'buyer_shipping_state' => $originalOrder->state ?? 'Delhi',
                'buyer_shipping_country' => $originalOrder->country ?? 'IN',
                'buyer_shipping_pincode' => $originalOrder->post_code ?? '',
                'buyer_shipping_email' => $originalOrder->email,
                'buyer_shipping_phone' => $originalOrder->phone,

                'buyer_pickup_first_name' => $originalOrder->first_name ?? '',
                'buyer_pickup_last_name' => $originalOrder->last_name ?? '',
                'buyer_pickup_address' => $originalOrder->address1 ?? '',
                'buyer_pickup_address_2' => $originalOrder->address2 ?? '',
                'buyer_pickup_city' => $originalOrder->city ?? 'Delhi',
                'buyer_pickup_state' => $originalOrder->state ?? 'Delhi',
                'buyer_pickup_country' => $originalOrder->country ?? 'IN',
                'buyer_pickup_pincode' => $originalOrder->post_code,
                'buyer_pickup_email' => $originalOrder->email,
                'buyer_pickup_phone' => $originalOrder->phone,

                'order_items' => $items,
                'payment_method' => $paymentMethod,
                'shipping_charges' => 0,
                'giftwrap_charges' => 0,
                'transaction_charges' => 0,
                'total_discount' => 0,
                'sub_total' => $subTotal,
                'return_reason' => $returnReasonId,
                'return_length' => 10,
                'return_breadth' => 10,
                'return_height' => 10,
                'return_weight' => 0.5,
                'exchange_length' => 10,
                'exchange_breadth' => 10,
                'exchange_height' => 10,
                'exchange_weight' => 0.5,
            ];

            if (!empty($channelId)) {
                $payload['channel_id'] = (int) $channelId;
            }

            $authToken = $this->getAuthToken();

            Log::channel('shiprocket')->info('Exchange Order Payload', $payload);

            $response = $this->client->post($this->url . '/orders/create/exchange', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::channel('shiprocket')->info('Exchange Order Response', $data);

            return response()->json([
                'status' => true,
                'message' => 'Shiprocket exchange order created successfully.',
                'shiprocket_response' => $data,
                'payload' => $payload,
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $error = $e->getResponse()
                ? json_decode($e->getResponse()->getBody()->getContents(), true)
                : $e->getMessage();

            Log::channel('shiprocket')->error('Exchange Order Error: ' . $e->getMessage(), [
                'return_request_id' => $returnRequest->id ?? null,
                'error' => $error,
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to create exchange order with Shiprocket.',
                'error' => $error,
            ], 500);
        } catch (\Exception $e) {
            Log::channel('shiprocket')->error('Exchange Order Error: ' . $e->getMessage(), [
                'return_request_id' => $returnRequest->id ?? null,
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
