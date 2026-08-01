<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\PaymentOrders;
use App\Models\PaymentRefund;
use App\Services\ShiprocketService;

class RazorpayWebhookController extends Controller
{
    protected function verifySignature(Request $request): void
    {
        $payload   = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $secret    = env('RAZORPAY_WEBHOOK_SECRET');

        log::channel('razorpay')->info('Verifying Razorpay Webhook Signature', [
            'payload_sha256' => hash('sha256', $payload),
            'signature_present' => ! empty($signature),
            'secret_present' => ! empty($secret),
        ]);

        $expected = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expected, (string) $signature)) {
            Log::channel('razorpay')->error('Webhook signature mismatch', [
                'signature_present' => ! empty($signature),
                'payload_sha256' => hash('sha256', $payload),
            ]);
            throw new \RuntimeException('Invalid Razorpay signature');
        }

        Log::channel('razorpay')->info('Webhook signature verified', [
            'payload_sha256' => hash('sha256', $payload),
        ]);
    }
    public function handle(Request $request)
    {
        try {
            $this->verifySignature($request);
            $payload = $request->all();

            Log::channel('razorpay')->info('Razorpay Webhook Payload', $payload);

            $event = $payload['event'] ?? null;

            switch ($event) {

                case 'payment.authorized':
                    $this->paymentAuthorized($payload);
                    break;

                case 'payment.captured':
                    $this->paymentCaptured($payload);
                    break;

                case 'payment.failed':
                    $this->paymentFailed($payload);
                    break;

                case 'refund.created':
                    $this->refundCreated($payload);
                    break;

                case 'refund.processed':
                    $this->refundProcessed($payload);
                    break;

                case 'refund.failed':
                    $this->refundFailed($payload);
                    break;

                default:
                    Log::channel('razorpay')->warning('Unhandled Razorpay Event: ' . $event);
                    break;
            }

            return response()->json([
                'success' => true
            ]);

        } catch (\Exception $e) {

            Log::channel('razorpay')->error('Razorpay Webhook Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT AUTHORIZED
    |--------------------------------------------------------------------------
    */
    private function paymentAuthorized($payload)
    {
        $payment = $payload['payload']['payment']['entity'];

        $paymentOrder = PaymentOrders::where(
            'razorpay_order_id',
            $payment['order_id']
        )->first();

        if (!$paymentOrder) {
            return;
        }

        $paymentOrder->update([
            'razorpay_payment_id' => $payment['id'],
            'payment_status'      => 'authorized',
            'payment_response'    => json_encode($payment),
        ]);

        Log::channel('razorpay')->info('Payment Authorized');
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT CAPTURED
    |--------------------------------------------------------------------------
    */
    private function paymentCaptured($payload)
    {
        $payment = $payload['payload']['payment']['entity'];
        $paymentOrder = PaymentOrders::where(
            'razorpay_order_id',
            $payment['order_id']
        )->first();

        if (!$paymentOrder) {
            return;
        }

        $paymentOrder->update([
            'razorpay_payment_id' => $payment['id'],
            'payment_status'      => 'paid',
            'payment_response'    => json_encode($payment),
        ]);

        $paymentOrder->order->update([
            'payment_status' => 'paid'
        ]);
        Log::channel('razorpay')->info('Payment Captured');

        $shiprocketService = new ShiprocketService(new \GuzzleHttp\Client());
        $shiprocketService->createCompleteShipmentForOrder($paymentOrder->order);
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT FAILED
    |--------------------------------------------------------------------------
    */
    private function paymentFailed($payload)
    {
        $payment = $payload['payload']['payment']['entity'];

        $paymentOrder = PaymentOrders::where(
            'razorpay_order_id',
            $payment['order_id']
        )->first();

        if (!$paymentOrder) {
            return;
        }

        $paymentOrder->update([
            'payment_status'   => 'failed',
            'payment_response' => json_encode($payment),
        ]);

        $paymentOrder->order->update([
            'payment_status' => 'failed',
            'status' => 'cancel',
        ]);

        Log::channel('razorpay')->info('Payment Failed');
    }

    /*
    |--------------------------------------------------------------------------
    | REFUND CREATED
    |--------------------------------------------------------------------------
    */
    private function refundCreated($payload)
    {
        $refund = $payload['payload']['refund']['entity'];

        $paymentOrder = PaymentOrders::where(
            'razorpay_payment_id',
            $refund['payment_id']
        )->first();

        if (!$paymentOrder) {
            return;
        }

        $existingRefund = PaymentRefund::where('razorpay_refund_id', $refund['id'])->first();
        if ($existingRefund) {
            $existingRefund->update([
                'refund_status' => $refund['status'],
                'refund_response' => json_encode($refund),
                'refund_amount' => $refund['amount'] / 100,
                'refund_reason' => $refund['notes']['reason'] ?? null,
                'refunded_at' => now(),
            ]);

            return;
        }

        PaymentRefund::create([
            'order_id'            => $paymentOrder->order_id,
            'payment_id'          => $paymentOrder->id,
            'razorpay_payment_id' => $refund['payment_id'],
            'razorpay_refund_id'  => $refund['id'],
            'refund_amount'       => $refund['amount'] / 100,
            'refund_status'       => $refund['status'],
            'refund_response'     => json_encode($refund),
            'refund_reason'       => $refund['notes']['reason'] ?? null,
            'refunded_at'         => now(),
        ]);

        Log::channel('razorpay')->info('Refund Created');
    }

    /*
    |--------------------------------------------------------------------------
    | REFUND PROCESSED
    |--------------------------------------------------------------------------
    */
    private function refundProcessed($payload)
    {
        $refund = $payload['payload']['refund']['entity'];

        $refundData = PaymentRefund::where(
            'razorpay_refund_id',
            $refund['id']
        )->first();

        if (!$refundData) {
            return;
        }

        $refundData->update([
            'refund_status'   => 'processed',
            'refund_response' => json_encode($refund),
            'refunded_at'     => now(),
        ]);

        $refundData->order->update([
            'payment_status' => 'refunded'
        ]);

        Log::channel('razorpay')->info('Refund Processed');
    }

    /*
    |--------------------------------------------------------------------------
    | REFUND FAILED
    |--------------------------------------------------------------------------
    */
    private function refundFailed($payload)
    {
        $refund = $payload['payload']['refund']['entity'];

        $refundData = PaymentRefund::where(
            'razorpay_refund_id',
            $refund['id']
        )->first();

        if (!$refundData) {
            return;
        }

        $refundData->update([
            'refund_status'   => 'failed',
            'refund_response' => json_encode($refund),
        ]);

        Log::channel('razorpay')->info('Refund Failed');
    }
}
