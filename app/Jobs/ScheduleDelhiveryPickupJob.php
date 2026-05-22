<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\ShipmentDetails;
use App\Services\DelhiveryService; // if you created service

class ScheduleDelhiveryPickupJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        Log::info("🚀 ScheduleDelhiveryPickupJob Started");

        try {
            // Fetch pending shipping orders
            $orders = ShipmentDetails::whereNull('pickup_id')
                                    
                                    ->where('shipment_awb', '!=', null)
                                    ->where('shipment_status' , 'Created')
                                    ->get();

            //Log::info("📦 Total orders found for pickup: ". $orders->count());
            $totalOrders = $orders->count();
            if ($orders->isEmpty()) {
                //Log::info("❌ No pending orders for pickup");
                return;
            }
            $response = (new DelhiveryService)->schedulePickup($totalOrders);
            log::info("📥 Pickup Scheduled Response: " . json_encode($response));
            if($response != null && !empty($response['pickup_id']))
            {
                $pickupId = $response['pickup_id'];
                // Update orders with pickup ID
                foreach ($orders as $order) {
                    $order->pickup_id = $pickupId;
                    $order->shipment_status = 'Scheduled';
                    $order->scheduled_at = now();
                    $order->save();
                }
                
                Log::info("🎉 ScheduleDelhiveryPickupJob Completed Successfully");
            }else{
                Log::error("🔥 Failed to schedule pickup: " . json_encode($response));
            }

        } catch (\Exception $e) {
            Log::error("🔥 ScheduleDelhiveryPickupJob Error: " . $e->getMessage());
        }
    }
}
