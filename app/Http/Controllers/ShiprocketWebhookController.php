<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderReturnRequest;

class ShiprocketWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::channel('shiprocket')->info(
            'Shiprocket Webhook',
            $payload
        );

        // RETURN ORDER WEBHOOK
        if (($payload['is_return'] ?? 0) == 1) {
            $this->updateReturnRequest($payload);
        } else {
            // FORWARD ORDER WEBHOOK
            $this->updateForwardOrder($payload);
        }

        return response()->json([
            'success' => true
        ], 200);
    }

    private function updateReturnRequest(array $payload)
    {
        $returnRequest = OrderReturnRequest::where(
            'awb_code',
            $payload['awb'] ?? null
        )
        ->orWhere(
            'shiprocket_return_order_id',
            $payload['order_id'] ?? null
        )
        ->first();

        if (!$returnRequest) {

            Log::channel('shiprocket')->warning(
                'Return Request Not Found',
                $payload
            );

            return;
        }

        $shiprocketStatus = strtoupper(
            trim($payload['current_status'] ?? '')
        );

        
        $status = match ($shiprocketStatus) {
            'RETURN PICKUP GENERATED' => 'return_pickup_generated',
            'RETURN PICKED UP' => 'return_picked_up',
            'IN TRANSIT' => 'return_in_transit',
            'OUT FOR DELIVERY' => 'return_out_for_delivery',
            'RETURN DELIVERED' => 'return_delivered',
            'QC PASSED' => 'qc_passed',
            'QC FAILED' => 'qc_failed',
            'REFUND PROCESSED' => 'refunded',

            default
                => strtolower(
                    str_replace(' ', '_', $shiprocketStatus)
                ),
        };

        $returnRequest->update([

            'status' => $status,
            'pickup_status'=> $payload['shipment_status'] ?? null,
            'current_tracking_status'=> $payload['current_status'] ?? null,
            'courier_name'=> $payload['courier_name'] ?? null,
            'awb_code'=> $payload['awb'] ?? null,
            'pickup_scheduled_date'=> $payload['pickup_scheduled_date'] ?? null,
            'tracking_payload'=> $payload,
        ]);


        if (strtolower($status) == 'return_picked_up') {
            $returnRequest->pickup_completed_at = now();
        }

        if (strtolower($status) == 'refunded') {
            $returnRequest->refunded_at = now();
        }

        $returnRequest->save();

        Log::channel('shiprocket')->info(
            'Return Request Updated Successfully',
            [
                'id' => $returnRequest->id,
                'status' => $status
            ]
        );
        // order deliverd on company then in razerpay call refund api when payment mode is online 
        //refundPayment --> call this function 
        // if (strtolower($status) == 'return_delivered') {

        // }

    }

    private function updateForwardOrder(array $payload)
    {
        $order = Order::where(
            'order_number',
            $payload['order_id'] ?? null
        )->first();

        if (!$order) {
            Log::channel('shiprocket')->warning(
                'Order Not Found',
                $payload
            );
            return;
        }

        $shiprocketStatus = strtoupper(
            trim($payload['current_status'] ?? '')
        );

        /**
         * ---------------------------------------------------------
         * MAP STATUS
         * ---------------------------------------------------------
         */
        $status = match ($shiprocketStatus) {
            'PICKUP GENERATED' => 'pickup_generated',
            'PICKED UP' => 'picked_up',
            'IN TRANSIT' => 'in_transit',
            'OUT FOR DELIVERY' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RTO DELIVERED' => 'returned',

            default
                => strtolower(
                    str_replace(' ', '_', $shiprocketStatus)
                ),
        };

        $order->update([
            'status' => $status,
        ]);

        Log::channel('shiprocket')->info(
            'Order Updated Successfully',
            [
                'order_id' => $order->id,
                'status' => $status
            ]
        );
    }
}