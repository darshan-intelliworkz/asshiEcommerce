<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\ShiprocketService;
use GuzzleHttp\Client;
use App\Models\ShipmentDetails;
use App\Models\PaymentOrders;
use App\Models\PaymentRefund;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    public function pay($order_id)
    {
        $order = Order::findOrFail($order_id);

        // Use your Razorpay sandbox credentials
        $key    = env('RAZORPAY_KEY');
        $secret = env('RAZORPAY_SECRET');
        $amount = round($order->total_amount);
        // Create Razorpay order using CURL
        $fields = [
            'amount'   => (int)$amount * 100, // in paise
            'currency' => 'INR',
            'receipt'  => 'receipt_' . $order->order_number,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/orders");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_USERPWD, $key . ":" . $secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

        $result = curl_exec($ch);
        \Log::info("result - " . json_encode($result)); 
        if (curl_errno($ch)) {
            return back()->with('error', curl_error($ch));
        }
        curl_close($ch);

        $response = json_decode($result, true);
        $payment = new PaymentOrders();
        $payment->order_id = $order->id;
        $payment->razorpay_order_id = $response['id'] ?? null;
        $payment->payment_status = 'pending';
        $payment->razorpay_payment_id = null;
        $payment->razorpay_signature = null;
        $payment->amount = $amount;
        $payment->transaction_id = null;
        $payment->payment_response = json_encode($response) ?? null;
        $payment->save();


        return view('frontend.razorpay.checkout-process', [
            'key'      => $key,
            'order_id' => $response['id'],
            'amount'   => $order->total_amount * 100,
            'order'    => $order
        ]);
    }

    public function verify(Request $request)
    {
        $signature = $request->razorpay_signature;
        $paymentId = $request->razorpay_payment_id;
        $orderId   = $request->razorpay_order_id;

        $order = Order::where('order_number', $request->order_number)->first();
        $order->payment_status = 'paid';
        $order->save();

        $payment = PaymentOrders::where('razorpay_order_id', $orderId)->first();
        if ($payment) {
            $payment->razorpay_payment_id = $paymentId;
            $payment->razorpay_signature = $signature;
            $payment->transaction_id = 'txn_' . $order->order_number;
            $payment->payment_status = 'paid';
            $payment->save();
        }

        // GENERATE SHIPPING PROCESS WITH SHIPROCKET
        $orderShipment = NULL;
        $client = new \GuzzleHttp\Client(); 
        $shiprocketService = new ShiprocketService(new Client());

        // CREATE SHIPMENT ORDER
        $orderResponse = $shiprocketService->createShipmentOrder($order);
        $orderResponseData = $orderResponse->getData(true);

        Log::info('Order Shipment Response: ', $orderResponseData);
        if($orderResponseData['status'] == true && isset($orderResponseData['shiprocket_response']) && strtolower($orderResponseData['shiprocket_response']['status']) == 'new' && $orderResponseData['shiprocket_response']['status_code'] == 1){
            $orderShipment = new ShipmentDetails();
            $orderShipment->order_id = $order->id;
            $orderShipment->order_number = $order->order_number;
            $orderShipment->shipment_status = 'New';
            $orderShipment->shipment_id = $orderResponseData['shiprocket_response']['shipment_id'] ?? NULL;
            $orderShipment->shipment_order_id = $orderResponseData['shiprocket_response']['order_id'] ?? NULL;
            $orderShipment->shipment_response = json_encode($orderResponseData) ?? NULL;
            
            // ASSIGN COURIER AWB NUMBER
            $orderAwbResponse = $shiprocketService->assignCourierAwb($orderShipment->shipment_id);
            $orderAwbResponseData = $orderAwbResponse->getData(true);
            log::info('Order AWB Response: ', $orderAwbResponseData);
            if (isset($orderAwbResponseData['status'],$orderAwbResponseData['shiprocket_response']['awb_assign_status'],$orderAwbResponseData['shiprocket_response']['response']['data']['awb_code']) && $orderAwbResponseData['status'] === true && $orderAwbResponseData['shiprocket_response']['awb_assign_status'] == 1) {
                $orderShipment->shipment_awb = $orderAwbResponseData['shiprocket_response']['response']['data']['awb_code'] ?? '';

                // GENERATE LABEL
                $orderLabelGenerateResponse = $shiprocketService->generateLabel($orderShipment->shipment_id);
                $orderLabelGenerateResponseData = $orderLabelGenerateResponse->getData(true);
                Log::info('Order Label Generate Response: ', $orderLabelGenerateResponseData);
                if (isset($orderLabelGenerateResponseData['shiprocket_response']['label_created'],$orderLabelGenerateResponseData['shiprocket_response']['label_url']) && $orderLabelGenerateResponseData['shiprocket_response']['label_created'] == 1 && $orderLabelGenerateResponseData['shiprocket_response']['label_url'] !== '') {
                    $orderShipment->label_pdf = $orderLabelGenerateResponseData['shiprocket_response']['label_url'] ?? NULL;
                    
                    // REQUEST FOR SHIPMENT PICKUP
                    if($orderShipment->pickup_request_response == null){
                        $orderShipmentPickupResponse = $shiprocketService->shipmentPickupRequest($orderShipment->shipment_id);
                        $orderShipmentPickupResponseData = $orderShipmentPickupResponse->getData(true);
                        Log::info('Order Shipment Pickup Response: ', $orderShipmentPickupResponseData);
                        if (isset($orderShipmentPickupResponseData['shiprocket_response']['pickup_status'],$orderShipmentPickupResponseData['shiprocket_response']['response']['pickup_scheduled_date']) && $orderShipmentPickupResponseData['shiprocket_response']['pickup_status'] == 1) {
                            $orderShipment->pickup_request_response = json_encode($orderShipmentPickupResponseData);
                            $orderShipment->scheduled_at = $orderShipmentPickupResponseData['shiprocket_response']['response']['pickup_scheduled_date'] ?? NULL;

                            // GENERATE MANIFEAST
                            // $orderGenerateMenifeastResponse = $shiprocketService->generateManifeast($orderShipment->shipment_id);
                            // $orderGenerateMenifeastResponseData = $orderGenerateMenifeastResponse->getData(true);
                            // if (isset($orderGenerateMenifeastResponseData['shiprocket_response']['status'],$orderGenerateMenifeastResponseData['shiprocket_response']['manifest_url']) && $orderGenerateMenifeastResponseData['status'] === true && $orderGenerateMenifeastResponseData['shiprocket_response']['status'] == 1 && $orderGenerateMenifeastResponseData['shiprocket_response']['manifest_url'] !== '') {
                            //     $orderShipment->manifest_url = $orderGenerateMenifeastResponseData['shiprocket_response']['manifest_url'] ?? NULL;
                            //     $orderShipment->shipment_status = 'Pickup Scheduled';
                            // }
                        }
                    }
                    // GENERATE MANIFEAST
                    $orderGenerateMenifeastResponse = $shiprocketService->generateManifeast($orderShipment->shipment_id);
                    $orderGenerateMenifeastResponseData = $orderGenerateMenifeastResponse->getData(true);
                    Log::info('Order Generate Manifest Response: ', $orderGenerateMenifeastResponseData);
                    if (isset($orderGenerateMenifeastResponseData['shiprocket_response']['status'],$orderGenerateMenifeastResponseData['shiprocket_response']['manifest_url']) && $orderGenerateMenifeastResponseData['status'] === true && $orderGenerateMenifeastResponseData['shiprocket_response']['status'] == 1 && $orderGenerateMenifeastResponseData['shiprocket_response']['manifest_url'] !== '') {
                        $orderShipment->manifest_url = $orderGenerateMenifeastResponseData['shiprocket_response']['manifest_url'] ?? NULL;
                        $orderShipment->shipment_status = 'Pickup Scheduled';
                    }
                    
                    // GENERATE INVOICE
                    $orderGenerateInvoiceResponse = $shiprocketService->generateInvoice($orderShipment->shipment_order_id);
                    $orderGenerateInvoiceResponseData = $orderGenerateInvoiceResponse->getData(true);
                    Log::info('Order Generate Invoice Response: ', $orderGenerateInvoiceResponseData);
                    if (isset($orderGenerateInvoiceResponseData['shiprocket_response']['invoice_url']) && $orderGenerateInvoiceResponseData['status'] == true && $orderGenerateInvoiceResponseData['shiprocket_response']['invoice_url'] !== '') {
                        $orderShipment->invoice_url = $orderGenerateInvoiceResponseData['shiprocket_response']['invoice_url'] ?? NULL;
                    }
                }
            }
            $orderShipment->save();
            $order->status = 'process';
            $order->save();
        }

        //return redirect()->route('myorders')->with('success', 'Payment Successful!');
        return response()->json([
            'status' => true,
            'message' => 'Payment verified successfully'
        ]);
    }

    public function cancel(Request $request)
    {
        Order::where('order_number', $request->order_number)
            ->update(['payment_status' => 'cancelled', 'status' => 'cancel']);

        return redirect()->route('myorders')->with('success', 'Payment Cancelled!');
    }

    public function failed(Request $request)
    {
        Order::where('order_number', $request->order_number)
            ->update(['payment_status' => 'cancelled', 'status' => 'cancel']);

        return redirect()->route('myorders')->with('success', 'Payment Failed!');
    }

   

    public function refundPayment($paymentId)
    {
        Log::info('Initiating refund for payment ID: ' . $paymentId);

        $payment = PaymentOrders::where('order_id', $paymentId)->firstOrFail();

        $razorpayPaymentId = $payment->razorpay_payment_id;

        Log::info('Payment record found: ' . $payment->amount .
            ' Razorpay ID: ' . $razorpayPaymentId);

        $captureResponse = $this->capturePayment($razorpayPaymentId, $payment->amount);
        if (!$captureResponse->getData()->status) {
            return response()->json([
                'status' => false,
                'message' => 'Payment capture failed. Refund cannot be processed.',
                'data' => $captureResponse->getData()
            ]);
        }
        $paymentDetails = $this->getPayment($razorpayPaymentId);

        Log::info('Razorpay Payment Details: ' . json_encode($paymentDetails));

        if ($paymentDetails['status'] !== 'captured') {
            return response()->json([
                'status' => false,
                'message' => 'Payment not captured'
            ]);
        }

        $key = env('RAZORPAY_KEY');
        $secret = env('RAZORPAY_SECRET');

        $fields = [
            'amount' => (int) $paymentDetails['amount']
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,
            "https://api.razorpay.com/v1/payments/" .
            $razorpayPaymentId .
            "/refund"
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_USERPWD, $key . ":" . $secret);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([]));

        $result = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        Log::info('Refund HTTP Code: ' . $httpCode);
        Log::info('Refund API Response: ' . $result);

        if (curl_errno($ch)) {
            Log::error(curl_error($ch));
        }

        curl_close($ch);

        $response = json_decode($result, true);

        if (isset($response['id'])) {

            PaymentRefund::create([
                'order_id' => $payment->order_id,
                'payment_id' => $payment->id,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_refund_id' => $response['id'],
                'refund_amount' => $payment->amount,
                'refund_status' => 'processed',
                'refund_response' => json_encode($response),
                'refund_reason' => 'Customer requested refund',
                'refunded_at' => now(),
            ]);

            $payment->update([
                'payment_status' => 'refunded'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Refund successful',
                'data' => $response
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => $response['error']['description'] ?? 'Refund failed',
            'data' => $response
        ]);
    }
    public function capturePayment($paymentId, $amount)
    {
        Log::info('Initiating capture for payment ID: ' . $paymentId);

        $key = env('RAZORPAY_KEY');
        $secret = env('RAZORPAY_SECRET');

        // Razorpay requires amount in paise
        $fields = [
            'amount' => $amount * 100,
            'currency' => 'INR'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/payments/" . $paymentId . "/capture");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_USERPWD, $key . ":" . $secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);

        $result = curl_exec($ch);

        Log::info('Capture API Response: ' . $result);

        if (curl_errno($ch)) {
            Log::error('Curl Error: ' . curl_error($ch));

            curl_close($ch);

            return response()->json([
                'status' => false,
                'message' => curl_error($ch)
            ]);
        }

        curl_close($ch);

        $response = json_decode($result, true);

        // Check success response
        if (isset($response['status']) && $response['status'] == 'captured') {

            PaymentOrders::where('razorpay_payment_id', $paymentId)->update([
                'payment_status' => 'Paid'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Payment captured successfully',
                'data' => $response
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => $response['error']['description'] ?? 'Payment capture failed',
            'data' => $response
        ]);
    }

    private function getPayment($paymentId)
    {
        $key = env('RAZORPAY_KEY');
        $secret = env('RAZORPAY_SECRET');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.razorpay.com/v1/payments/" . $paymentId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $key . ":" . $secret);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('Razorpay getPayment CURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($result, true);
    }
}
