<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\ShiprocketService;
use GuzzleHttp\Client;
use App\Models\PaymentOrders;
use App\Models\PaymentRefund;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    private function cancelRazorpayOrder(string $orderNumber, string $paymentStatus = 'failed'): ?Order
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return null;
        }

        $order->update([
            'payment_status' => $paymentStatus,
            'status' => 'cancel',
        ]);

        PaymentOrders::where('order_id', $order->id)
            ->whereIn('payment_status', ['pending', 'failed', 'refunded', 'paid'])
            ->get()
            ->each(function ($paymentOrder) use ($paymentStatus) {
                $paymentOrder->update([
                    'payment_status' => $paymentStatus,
                ]);
            });

        return $order;
    }

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
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'order_number' => 'nullable|string',
        ]);

        $signature = $request->razorpay_signature;
        $paymentId = $request->razorpay_payment_id;
        $orderId   = $request->razorpay_order_id;

        // Razorpay payment verification uses: order_id | payment_id
        $payload = $orderId . '|' . $paymentId;
        $expectedSignature = hash_hmac('sha256', $payload, env('RAZORPAY_SECRET'));

        if (!hash_equals($expectedSignature, (string) $signature)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Razorpay signature'
            ], 400);
        }

        $payment = PaymentOrders::where('razorpay_order_id', $orderId)->first();
        if (!$payment || !$payment->order || $payment->order->order_number !== $request->order_number) {
            return response()->json([
                'status' => false,
                'message' => 'Payment order not found'
            ], 404);
        }

        $order = $payment->order;
        $order->payment_status = 'paid';
        $order->save();

        $payment->razorpay_payment_id = $paymentId;
        $payment->razorpay_signature = $signature;
        $payment->transaction_id = 'txn_' . $order->order_number;
        $payment->payment_status = 'paid';
        $payment->save();

        $shiprocketService = new ShiprocketService(new Client());
        $shiprocketService->createCompleteShipmentForOrder($order);

        session()->put('thank_you_order_id', $order->id);
        session()->forget('cart');
        session()->forget('coupon');

        return response()->json([
            'status' => true,
            'message' => 'Payment verified successfully',
            'redirect_url' => route('thank.you', ['order_id' => $order->id])
        ]);
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $this->cancelRazorpayOrder($request->order_number, 'cancelled');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Payment Cancelled!',
                'redirect_url' => route('myorders'),
            ]);
        }

        return redirect()->route('myorders')->with('success', 'Payment Cancelled!');
    }

    public function failed(Request $request)
    {
        if (!$request->filled('order_number')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment failed, but order details were missing. Please contact support if your order is still pending.',
                    'redirect_url' => route('myorders'),
                ], 422);
            }

            return redirect()->route('myorders')->with('error', 'Payment failed, but order details were missing. Please contact support if your order is still pending.');
        }

        $this->cancelRazorpayOrder($request->order_number, 'failed');

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Payment Failed!',
                'redirect_url' => route('myorders'),
            ]);
        }

        return redirect()->route('myorders')->with('error', 'Payment Failed!');
    }

   

    public function refundPayment($orderId, $refundAmount = null)
    {
        $payment = PaymentOrders::where('order_id', $orderId)->firstOrFail();
        $razorpayPaymentId = $payment->razorpay_payment_id;
        Log::info('Payment record found: ' . $payment->amount .
            ' Razorpay ID: ' . $razorpayPaymentId);

        // we have implatented auto capture on razerpay dashboard, so this comment but dont remove for need in future  
        // $captureResponse = $this->capturePayment($razorpayPaymentId, $payment->amount);
        // if (!$captureResponse->getData()->status) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Payment capture failed. Refund cannot be processed.',
        //         'data' => $captureResponse->getData()
        //     ]);
        // }
        $paymentDetails = $this->getPayment($razorpayPaymentId);
        Log::info('Razorpay Payment Details: ' . json_encode($paymentDetails));
        if ($paymentDetails['status'] !== 'captured') {
            // $captureResponse = $this->capturePayment($razorpayPaymentId, $payment->amount);
            // if (!$captureResponse->getData()->status) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Payment capture failed. Refund cannot be processed.',
            //         'data' => $captureResponse->getData()
            //     ]);
            // }
            return response()->json([
                'status' => false,
                'message' => 'Payment not captured'
            ]);
        }

        $key = env('RAZORPAY_KEY');
        $secret = env('RAZORPAY_SECRET');

        $fields = [];
        if ($refundAmount !== null) {
            $fields['amount'] = (int) round($refundAmount * 100);
        }
        $ch = curl_init();
        $refundUrl = "https://api.razorpay.com/v1/payments/".$razorpayPaymentId ."/refund";
        
        curl_setopt($ch, CURLOPT_URL, $refundUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_USERPWD, $key . ":" . $secret);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

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
                'refund_amount' => $refundAmount ?? $payment->amount,
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

    // refund process for COD orders
    public function createContact($name, $email, $mobile)
    {
        $key = env('RAZORPAYX_KEY');
        $secret = env('RAZORPAYX_SECRET');

        $payload = [
            'name'    => $name,
            'email'   => $email,
            'contact' => $mobile,
            'type'    => 'customer',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/contacts');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $key . ':' . $secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        Log::channel('razorpay')->info('Create Contact HTTP Code: ' . $httpCode);
        Log::channel('razorpay')->info('Create Contact Response: ' . $result);

        curl_close($ch);

        return json_decode($result, true);
    }

    public function createUpiFundAccount($contactId, $upiId)
    {
        $key = env('RAZORPAYX_KEY');
        $secret = env('RAZORPAYX_SECRET');

        $payload = [
            'contact_id'  => $contactId,
            'account_type' => 'vpa',
            'vpa' => [
                'address' => $upiId
            ]
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/fund_accounts');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $key . ':' . $secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        Log::channel('razorpay')->info('Create Fund Account HTTP Code: ' . $httpCode);
        Log::channel('razorpay')->info('Create Fund Account Response: ' . $result);

        curl_close($ch);

        return json_decode($result, true);
    }

    public function createPayout(
        $fundAccountId,
        $amount,
        $referenceId
    ) {
        $key = env('RAZORPAYX_KEY');
        $secret = env('RAZORPAYX_SECRET');

        $payload = [
            'account_number' => env('RAZORPAYX_ACCOUNT_NUMBER'),
            'fund_account_id' => $fundAccountId,
            'amount' => $amount * 100,
            'currency' => 'INR',
            'mode' => 'UPI',
            'purpose' => 'refund',
            'queue_if_low_balance' => true,
            'reference_id' => $referenceId,
            'narration' => 'Order Return Refund'
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/payouts');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, $key . ':' . $secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        Log::channel('razorpay')->info('Create Payout HTTP Code: ' . $httpCode);
        Log::channel('razorpay')->info('Create Payout Response: ' . $result);

        curl_close($ch);

        return json_decode($result, true);
    }
}
