<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderReturnRequest;
use App\Models\ShipmentDetails;

class ShiprocketWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::channel('shiprocket')->info(
            'Shiprocket Webhook',
            $payload
        );

        $exchangeRequest = $this->findExchangeRequest($payload);

        // EXCHANGE ORDER WEBHOOK
        if ($exchangeRequest) {
            Log::channel('shiprocket')->info(
                'Identified as Exchange Order Webhook',
                [
                    'return_request_id' => $exchangeRequest->id,
                    'payload' => $payload,
                ]
            );
            $this->updateExchangeRequest($exchangeRequest, $payload);
        } elseif (($payload['is_return'] ?? 0) == 1) {
            // RETURN ORDER WEBHOOK
            Log::channel('shiprocket')->info(
                'Identified as Return Order Webhook',
                $payload
            );
            $this->updateReturnRequest($payload);
        } else {
            // FORWARD ORDER WEBHOOK
            Log::channel('shiprocket')->info(
                'Identified as Forward Order Webhook',
                $payload
            );
            $this->updateForwardOrder($payload);
        }

        return response()->json([
            'success' => true
        ], 200);
    }

    private function updateReturnRequest(array $payload)
    {
        Log::channel('shiprocket')->info(
            'Processing Return Order Webhook',
            $payload
        );
        $returnRequest = OrderReturnRequest::where('return_type', 'return')
            ->where(function ($query) use ($payload) {
                if (!empty($payload['awb'])) {
                    $query->where('awb_code', $payload['awb']);
                }

                if (!empty($payload['sr_order_id'])) {
                    $query->orWhere('shiprocket_return_order_id', $payload['sr_order_id']);
                }
            })
            ->first();

        Log::channel('shiprocket')->info('Return Request Lookup', [
            'awb' => $payload['awb'] ?? null,
            'sr_order_id' => $payload['sr_order_id'] ?? null,
            'found' => !empty($returnRequest),
        ]);

        Log::channel('shiprocket')->info(
            'Return Request data: ' . json_encode($returnRequest)
        );

        if (!$returnRequest) {
            Log::channel('shiprocket')->warning(
                'Return Request Not Found',
                $payload
            );
            //$this->cancelOrder($payload);
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
            'RETURN CANCELED' => 'return_cancelled',
            'RETURN CANCELLED' => 'return_cancelled',

            default
                => strtolower(
                    str_replace(' ', '_', $shiprocketStatus)
                ),
        };

        Log::channel('shiprocket')->info(
            'Mapped Status: ' . $status
        );
        $returnRequest->update([
            'status' => $status,
            'pickup_status'=> $payload['shipment_status'] ?? null,
            'current_tracking_status'=> $payload['current_status'] ?? null,
            'courier_name'=> $payload['courier_name'] ?? null,
            'awb_code'=> $payload['awb'] ?? null,
            'pickup_scheduled_date'=> $payload['pickup_scheduled_date'] ?? null,
            'tracking_payload'=> $payload,
        ]);

        $shipmentDetails = ShipmentDetails::where(
            'order_id',
            $returnRequest->order_id ?? null
        )->first();

        if ($shipmentDetails) {
            $shipmentDetails->update([
                'shipment_status' => $status,
                'shipment_response' => $payload
            ]);
        }

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

        // TEMPORARY COMMENTED DUE TO THIS WILL CALL BY ADMIN AS OF NOW
        // order deliverd on company then in razerpay call refund api when payment mode is online 
        //refundPayment --> call this function
        $razorpayController = new RazorpayController();
        if ($returnRequest->order->payment_method == 'razorpay' && $status == 'return_delivered') {
            // $razerpayrefund = $razorpayController->refundPayment($returnRequest->order->id);
            //     Log::channel('shiprocket')->info('Refund Initiated for Order ID: ' . $razerpayrefund);
        }
        
        // on cod order deliverd on company then in razerpay call refund api
        if ($returnRequest->order->payment_method == 'cod' && $status == 'return_delivered') {
            // INSTEAD OR RAZORPAY PAYOUT AS OF NOW ADMIN WILL PAY MANUALLU VIA UPI ID
            // $customerDeatils = $returnRequest->order;
            // Log::channel('shiprocket')->info('Customer Details: ' . json_encode($customerDeatils));
            // $fullname = $customerDeatils->first_name . ' ' . $customerDeatils->last_name;
            // $contact = $razorpayController->createContact(
            //     $fullname,
            //     $customerDeatils->email,
            //     $customerDeatils->phone
            // );
            // Log::channel('shiprocket')->info('Contact Created with ID: ' . $contact);

            // $fundAccount = $razorpayController->createUpiFundAccount(
            //     $contact['id'],
            //     $returnRequest->refund_upi_id
            // );

            // $payout = $razorpayController->createPayout(
            //     $fundAccount['id'],
            //     $returnRequest->refund_amount,
            //     'RETURN_' . $returnRequest->id
            // );
        }
        Log::channel('shiprocket')->info(
            'Refund Processed for Return Request ID: ' . $returnRequest->id
        );
    }

    private function findExchangeRequest(array $payload)
    {
        if (
            empty($payload['awb']) &&
            empty($payload['sr_order_id']) &&
            empty($payload['shipment_id'])
        ) {
            return null;
        }

        return OrderReturnRequest::where('return_type', 'exchange')
            ->where(function ($query) use ($payload) {
                if (!empty($payload['sr_order_id'])) {
                    $query->where('exchange_order_id', $payload['sr_order_id']);
                }

                if (!empty($payload['shipment_id'])) {
                    $query->orWhere('exchange_shipment_id', $payload['shipment_id']);
                }

                if (!empty($payload['awb'])) {
                    $query->orWhere('awb_code', $payload['awb']);
                }
            })
            ->first();
    }

    private function updateExchangeRequest(OrderReturnRequest $returnRequest, array $payload)
    {
        Log::channel('shiprocket')->info(
            'Processing Exchange Order Webhook',
            [
                'return_request_id' => $returnRequest->id,
                'payload' => $payload,
            ]
        );

        $shiprocketStatus = strtoupper(
            trim($payload['current_status'] ?? '')
        );

        $status = match ($shiprocketStatus) {
            'PICKUP GENERATED',
            'RETURN PICKUP GENERATED' => 'exchange_pickup_generated',
            'PICKED UP',
            'RETURN PICKED UP' => 'exchange_picked_up',
            'IN TRANSIT',
            'RETURN IN TRANSIT' => 'exchange_in_transit',
            'OUT FOR DELIVERY' => 'exchange_out_for_delivery',
            'RETURN DELIVERED' => 'exchange_return_delivered',
            'DELIVERED' => 'exchange_delivered',
            'QC PASSED' => 'exchange_qc_passed',
            'QC FAILED' => 'exchange_qc_failed',
            'CANCELED',
            'CANCELLED',
            'RETURN CANCELED',
            'RETURN CANCELLED' => 'exchange_cancelled',

            default => $shiprocketStatus
                ? 'exchange_' . strtolower(str_replace(' ', '_', $shiprocketStatus))
                : $returnRequest->status,
        };

        $updateData = [
            'status' => $status,
            'pickup_status' => $payload['shipment_status'] ?? null,
            'current_tracking_status' => $payload['current_status'] ?? null,
            'courier_name' => $payload['courier_name'] ?? $returnRequest->courier_name,
            'pickup_scheduled_date' => $payload['pickup_scheduled_date'] ?? $returnRequest->pickup_scheduled_date,
            'tracking_payload' => $payload,
        ];

        if (!empty($payload['awb'])) {
            $updateData['awb_code'] = $payload['awb'];
        }

        if (!empty($payload['sr_order_id'])) {
            $updateData['exchange_order_id'] = $payload['sr_order_id'];
        }

        if (!empty($payload['shipment_id'])) {
            $updateData['exchange_shipment_id'] = $payload['shipment_id'];
        }

        if ($status === 'exchange_picked_up' && empty($returnRequest->pickup_completed_at)) {
            $updateData['pickup_completed_at'] = now();
        }

        $returnRequest->update($updateData);

        if ($returnRequest->order) {
            $returnRequest->order->update([
                'status' => $this->mapExchangeOrderStatus($status),
            ]);
        }

        Log::channel('shiprocket')->info(
            'Exchange Request Updated Successfully',
            [
                'id' => $returnRequest->id,
                'status' => $status,
                'order_status' => $returnRequest->order->status ?? null,
            ]
        );
    }

    private function mapExchangeOrderStatus(string $exchangeStatus): string
    {
        return match ($exchangeStatus) {
            'exchange_pickup_generated' => 'exchange pickup generated',
            'exchange_picked_up' => 'exchange picked up',
            'exchange_in_transit' => 'exchange in transit',
            'exchange_return_delivered' => 'exchange return delivered',
            'exchange_qc_passed' => 'exchange qc passed',
            'exchange_qc_failed' => 'exchange qc failed',
            'exchange_out_for_delivery' => 'exchange out for delivery',
            'exchange_delivered' => 'exchange delivered',
            'exchange_cancelled' => 'exchange cancelled',
            default => str_replace('_', ' ', $exchangeStatus),
        };
    }

    private function updateForwardOrder(array $payload)
    {
        $shipmentDetails = ShipmentDetails::where(
            'shipment_order_id',
            $payload['sr_order_id'] ?? null
        )->first();


        $order = Order::where('id', $shipmentDetails->order_id ?? null)->first();

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
            'PICKUP GENERATED',
            'PICKED UP',
            'IN TRANSIT' => 'process',
            'OUT FOR DELIVERY' => 'out for delivery',
            'DELIVERED' => 'delivered',
            'CANCELED',
            'CANCELLED' => 'cancel',

            default => $order->status,
        };

        $orderUpdateData = ['status' => $status];
        if ($status === 'delivered' && empty($order->delivered_at)) {
            $orderUpdateData['delivered_at'] = now();
        }

        $order->update($orderUpdateData);

        $shipmentDetails->update([
            'shipment_status' => $status,
            'shipment_response' => $payload
        ]);

        Log::channel('shiprocket')->info(
            'Order Updated Successfully',
            [
                'order_id' => $order->id,
                'status' => $status
            ]
        );
    }

    private function cancelOrder($payload)
    {
        Log::channel('shiprocket')->info(
            'Cancel Shipment Order Request',
            $payload
        );
        $shipmentDetailsUpdate = ShipmentDetails::where(
            'shipment_order_id',
            $payload['sr_order_id'] ?? null
        )->first();

        Log::channel('shiprocket')->info('Shipment Details Lookup for Cancellation', [
            'shipment_order_id' => $payload['sr_order_id'] ?? null,
            'found' => !empty($shipmentDetailsUpdate),
        ]);
        if (!$shipmentDetailsUpdate) {
            Log::channel('shiprocket')->warning(
                'Shipment Details Not Found for Cancellation',
                $payload
            );
            return;
        }

        $shipmentDetailsUpdate->update([
            'shipment_status' => 'canceled',
            'shipment_response' => $payload
        ]);

        $order = Order::where('id', $shipmentDetailsUpdate->order_id)->first();

        log::channel('shiprocket')->info('Order Lookup for Cancellation', [
            'order_id' => $shipmentDetailsUpdate->order_id,
            'found' => !empty($order),
        ]);
        if (!$order) {
            Log::channel('shiprocket')->warning(
                'Order Not Found for Cancellation',
                $payload
            );
            return;
        }

        $order->update([
            'status' => 'cancel',
        ]);

        Log::channel('shiprocket')->info(
            'Order Canceled Successfully',
            [
                'order_id' => $order->id,
                'status' => 'canceled'
            ]
        );
    }
}
