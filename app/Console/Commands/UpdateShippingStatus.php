<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\ShipmentDetails;
use App\Services\ShiprocketService;
use GuzzleHttp\Client;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class UpdateShippingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:shipping-status';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will update shipment Status';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        \Log::info("SHIPPING STATUS CRON STARTED");

        $client = new \GuzzleHttp\Client(); 
        $shiprocketService = new ShiprocketService(new Client());
        $shiprocketStatusMap = [1  => 'new', 2  => 'invoiced', 3  => 'ready to ship', 4  => 'pickup scheduled', 5  => 'canceled',
                                6  => 'shipped', 7  => 'delivered', 8  => 'epayment failed', 9  => 'returned', 15 => 'rto initiated',
                                19 => 'out for delivery', 20 => 'in transit', 33 => 'lost', 36 => 'undelivered', 37 => 'delivery delayed',
                                39 => 'destroyed', 40 => 'damaged', 43 => 'reached destination hub', 51 => 'picked up', 54 => 'canceled before dispatched'];

        $trackableStatuses = ['new', 'ready to ship', 'pickup scheduled', 'pickup queue', 'pickup rescheduled', 'out for pickup', 'picked up', 'shipped', 'in transit', 'out for delivery', 'reached destination hub', 'undelivered', 'delivery delayed'];
        $shipments = ShipmentDetails::whereNotNull('shipment_awb')->whereNotNull('shipment_id')->whereIn(\DB::raw('LOWER(shipment_status)'), $trackableStatuses)->get();
        if(isset($shipments) && is_countable($shipments) && count($shipments) > 0){
            foreach($shipments as $key => $val){
                $order = Order::where('id', $val->order_id)->first();
                $response = $shiprocketService->getShipmentStatus($val->shipment_id);
                $responseData = $response->getData(true);
                Log::info('Shipment Status Response for Shipment ID '.$val->shipment_id.': ', $responseData); 
                if (isset($responseData['status'],$responseData['shiprocket_response']['data'],$responseData['shiprocket_response']['data']['status']) && $responseData['status'] === true && $responseData['shiprocket_response']['data']['status'] != '') {
                    $statusId = $responseData['shiprocket_response']['data']['status'] ?? null;
                    if (!$statusId) {
                        continue;
                    }
                    // Ignore return & RTO statuses
                    $ignoreStatusIds = [9, 15, 16, 17, 18, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32];
                    if (in_array($statusId, $ignoreStatusIds)) {
                        continue;
                    }
                    $statusText = $shiprocketStatusMap[$statusId] ?? 'unknown';
                    if($statusText == 'out for delivery' || $statusText == 'delivered'){
                        $order->status = $statusText;
                    }
                    $order->save();
                    $val->shipment_status = $statusText;
                    $val->save();
                }else{
                    continue;
                }
            }
        }

        \Log::info("SHIPPING STATUS CRON ENDED");
    }
}
