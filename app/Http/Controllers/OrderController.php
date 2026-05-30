<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use App\User;
use PDF;
use Notification;
use Helper;
use Illuminate\Support\Str;
use App\Notifications\StatusNotification;
use App\Http\Controllers\DelhiveryController;
use App\Models\ShipmentDetails;
use App\Http\Controllers\RazorpayController;
use App\Models\OrderReturnRequest;
use App\Services\ShiprocketService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders=Order::orderBy('id','DESC')->paginate(10);
        return view('backend.order.index')->with('orders',$orders);
    }

   
    public function store(Request $request , DelhiveryController $delhivery) 
    {
        $this->validate($request,[
            'first_name'=>'string|required',
            'last_name'=>'string|required',
            'address1'=>'string|required',
            'address2'=>'string|nullable',
            'coupon'=>'nullable|numeric',
            'phone'=>'numeric|required',
            'post_code'=>'required|string',
            'email'=>'string|required',
            'payment_method' => 'required',
            //'country' => 'required',
            'state' => 'required',
            'city' => 'required',
        ]);
        if(empty(Cart::where('user_id',auth()->user()->id)->where('order_id',null)->first())){
            request()->session()->flash('error','Cart is Empty !');
            return back();
        }
        
        $order=new Order();
        $order_data=$request->all();
        $order_data['order_number']='ORD-'.strtoupper(Str::random(10));
        $order_data['user_id']=$request->user()->id;
        // $order_data['shipping_id']=$request->shipping ?? '';
        $shipping= $request->shipping ?? 0;
        $order_data['shiping_charges']= $request->shipping ?? 0;
        // return session('coupon')['value'];
        $order_data['sub_total']=Helper::totalCartPrice();
        $order_data['quantity']=Helper::cartCount();
        $gstTotal = Helper::totalGstPrice();
        if(session('coupon')){
            $order_data['coupon']=session('coupon')['value'];
        }
        if($request->shipping){
            if(session('coupon')){
                $order_data['total_amount']=Helper::totalCartPrice()+$shipping-session('coupon')['value'];
            }
            else{
                $order_data['total_amount']=Helper::totalCartPrice()+$shipping;
            }
        }
        else{
            if(session('coupon')){
                $order_data['total_amount']=Helper::totalCartPrice()-session('coupon')['value'];
            }
            else{
                $order_data['total_amount']=Helper::totalCartPrice();
            }
        }
        $order_data['total_amount'] += $gstTotal;
        // return $order_data['total_amount'];
        $order_data['status']="new";
        if(request('payment_method')=='paypal'){
            $order_data['payment_method']='paypal';
            //$order_data['payment_status']='paid';
        }else if(request('payment_method')=='razorpay'){
            $order_data['payment_method']='razorpay';
            //$order_data['payment_status']='paid';
        }else{
            $order_data['payment_method']='cod';
            //$order_data['payment_status']='Unpaid';
        }
        $order_data['payment_status']='unpaid';
        $order_data['country']='IN';
        $order->fill($order_data);
        
        $order->total_gst_amount = $gstTotal;
        $order->save();
        //$status=$order->save();

        

        if($order)
        $users=User::where('role','admin')->first();
        $details=[
            'title'=>'New order created',
            'actionURL'=>route('order.show',$order->id),
            'fas'=>'fa-file-alt'
        ];
        //Notification::send($users, new StatusNotification($details));
        if(request('payment_method')=='paypal'){
            return redirect()->route('payment')->with(['id'=>$order->id]);
        }
        else{
            session()->forget('cart');
            session()->forget('coupon');
        }
        Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);
        
        // GENERATE SHIPPING PROCESS WITH SHIPROCKET
         if (strtolower($order->payment_method) == 'cod') {
            $orderShipment = NULL;
            $client = new \GuzzleHttp\Client(); 
            $shiprocketService = new ShiprocketService(new Client());
            $orderResponse = $shiprocketService->createShipmentOrder($order);
            $orderResponseData = $orderResponse->getData(true);
           
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
                Log::info('Order AWB Response: ', $orderAwbResponseData);
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
                                // // GENERATE MANIFEAST
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
        }


        if (request('payment_method') == 'razorpay') {
            $razorpayController = new RazorpayController();
            return $razorpayController->pay($order->id);
        }
        // dd($users);        
        request()->session()->flash('success','Your product successfully placed in order');
        return redirect()->route('home');
    }

   
    public function show($id)
    {
        $order=Order::find($id);
        $shipmentDetails = ShipmentDetails::where('order_id', $id)->first();
        return view('backend.order.show', compact('order', 'shipmentDetails'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order=Order::find($id);
        return view('backend.order.edit')->with('order',$order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order=Order::find($id);
        $this->validate($request,[
            'status'=>'required|in:new,process,delivered,cancel'
        ]);
        $data=$request->all();
        // return $request->status;
        if($request->status=='delivered'){
            foreach($order->cart as $cart){
                $product=$cart->product;
                // return $product;
                $product->stock -=$cart->quantity;
                $product->save();
            }
        }
        $status=$order->fill($data)->save();
        if($status){
            request()->session()->flash('success','Successfully updated order');
        }
        else{
            request()->session()->flash('error','Error while updating order');
        }
        return redirect()->route('order.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order=Order::find($id);
        if($order){
            $status=$order->delete();
            if($status){
                request()->session()->flash('success','Order Successfully deleted');
            }
            else{
                request()->session()->flash('error','Order can not deleted');
            }
            return redirect()->route('order.index');
        }
        else{
            request()->session()->flash('error','Order can not found');
            return redirect()->back();
        }
    }

    public function orderTrack(){
        $orders = [];
        if(auth()->check()){
            $orders = Order::select('id', 'order_number', 'status')->where('status', '!=', 'cancel')->where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->get();
        }
            
        return view('frontend.pages.order-track', compact('orders'));
    }

    public function productTrackOrder(Request $request){
        // return $request->all();
        // if(auth()->check()){
        //     $order=Order::where('user_id',auth()->user()->id)->where('order_number',$request->order_number)->first();
        //     if($order){
        //         if($order->status=="new"){
        //         request()->session()->flash('success','Your order has been placed. please wait.');
        //         return redirect()->route('home');

        //         }
        //         elseif($order->status=="process"){
        //             request()->session()->flash('success','Your order is under processing please wait.');
        //             return redirect()->route('home');
        
        //         }
        //         elseif($order->status=="delivered"){
        //             request()->session()->flash('success','Your order is successfully delivered.');
        //             return redirect()->route('home');
        
        //         }
        //         else{
        //             request()->session()->flash('error','Your order canceled. please try again');
        //             return redirect()->route('home');
        
        //         }
        //     }
        //     else{
        //         request()->session()->flash('error','Invalid order numer please try again');
        //         return back();
        //     }
        // }else{
        //     request()->session()->flash('error','Please login before track order');
        //     return back();
        // }   
        if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please login before track order'
            ], 401);
        }

        $order = Order::where('user_id', auth()->id())
            ->where('order_number', $request->order_number)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid order number'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'order_status' => $order->status
        ]);

    }

    // PDF generate
    public function pdf(Request $request){
        $order=Order::getAllOrder($request->id);
        // return $order;
        $file_name=$order->order_number.'-'.$order->first_name.'.pdf';
        // return $file_name;
        $pdf=PDF::loadview('backend.order.pdf',compact('order'));
        return $pdf->download($file_name);
    }
    // Income chart
    public function incomeChart(Request $request){
        $year=\Carbon\Carbon::now()->year;
        // dd($year);
        $items=Order::with(['cart_info'])->whereYear('created_at',$year)->where('status','delivered')->get()
            ->groupBy(function($d){
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });
            // dd($items);
        $result=[];
        foreach($items as $month=>$item_collections){
            foreach($item_collections as $item){
                $amount=$item->cart_info->sum('amount');
                // dd($amount);
                $m=intval($month);
                // return $m;
                isset($result[$m]) ? $result[$m] += $amount :$result[$m]=$amount;
            }
        }
        $data=[];
        for($i=1; $i <=12; $i++){
            $monthName=date('F', mktime(0,0,0,$i,1));
            $data[$monthName] = (!empty($result[$i]))? number_format((float)($result[$i]), 2, '.', '') : 0.0;
        }
        return $data;
    }
    
    public function myOrders(Request $request){
        $orders = Order::where('user_id',auth()->user()->id)->orderBy('id','DESC')->paginate(10);
        return view('frontend.pages.myorders', compact('orders'));
    }

    public function orderDetails($id){
        $order = Order::where('id', $id)->with(['cart', 'cart.color', 'user'])->first();
        return view('frontend.pages.order_details', compact('order'));
    }
    
    public function orderUpdate(Request $request){
        $order = Order::find($request->id);
        if(!$order){
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ]);
        }

        if($request->status == 'Cancell'){
            return $this->cancelOrder($request->id);
        } else if($request->status == 'Return') {
            return $this->returnOrder($request->id);
        } else if($request->status == 'Return Cancel') {
            return $this->cancelReturnRequest($request->id);
         } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid status update request'
             ]);
        }
        
    }

    public function cancelOrder($orderId){
        $order = Order::find($orderId);
        $shipmentDetails = ShipmentDetails::where('order_id', $orderId)->first();
        $shiprocketService = new ShiprocketService(new Client());
        $returnResponse = $shiprocketService->cancelShipmentOrder($shipmentDetails->shipment_order_id);
        $returnData = $returnResponse->getData(true);
        Log::channel('shiprocket')->info('Cancel Shipment Response', $returnData);
        if (!$returnData['status']) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to cancel order in Shiprocket'
            ]);
        }

        if($order->payment_method == 'cod'){
            $order->status = 'cancel';
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled successfully'
            ]);
        }
        else if($order->payment_method == 'razorpay' && $order->payment_status == 'paid'){
            $razorpayController = new RazorpayController();
            $refundResponse = $razorpayController->refundPayment($order->id);
            $refundResponseData = $refundResponse->getData(true);

            if (isset($refundResponseData['status']) && $refundResponseData['status'] === true) {
                $order->status = 'cancel';
                $order->payment_status = 'refunded';
                $order->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Order cancelled and refund initiated successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Refund failed, order cancellation unsuccessful'
                ]);
            }
        }
        else{
            return response()->json([
                'status' => false,
                'message' => 'Invalid payment method for cancellation'
            ]);
        }
    }

    public function returnExchange(Request $request)
    {
        $order = Order::find($request->order_id);

        if (!$order) {
            request()->session()->flash('error', 'Order not found');
            return back();
        }

        $validationRules = [
            'order_id'      => 'required|exists:orders,id',
            'request_type'  => 'required',
            'reason'        => 'required|string',
            'notes'         => 'nullable|string',
            'images.*'      => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        // If payment method is COD then UPI ID required
        if ($order->payment_method == 'cod') {

            $validationRules['upi_id'] = [
                'required',
                'regex:/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/'
            ];

        } else {

            $validationRules['upi_id'] = 'nullable';

        }

        $request->validate($validationRules, [
            'upi_id.required' => 'UPI ID is required for COD orders.',
            'upi_id.regex'    => 'Please enter a valid UPI ID.',
        ]);

        $uploadedImages = [];

        if ($request->hasFile('images')) {

            $destinationPath = public_path('return_order_images');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            foreach ($request->file('images') as $image) {

                $imageName = time() . '_' . rand(1111,9999) . '.' . $image->getClientOriginalExtension();

                $image->move($destinationPath, $imageName);

                $uploadedImages[] = 'return_order_images/' . $imageName;
            }
        }

        OrderReturnRequest::create([
            'order_id'          => $order->id,
            'return_type'       => $request->request_type,
            'reason'            => $request->reason,
            'customer_comment'  => $request->notes,
            'upi_id'            => $request->upi_id,
            'images'            => json_encode($uploadedImages),
            'status'            => 'pending',
        ]);

        $order->status = 'return request';
        $order->save();

        if($request->request_type == 'return'){
            $this->returnOrder($order->id);
        }

        request()->session()->flash(
            'success',
            'Your return/exchange request has been submitted successfully'
        );

        return back();
    }


    public function returnOrder($orderId)
    {
        DB::beginTransaction();

        try {

            Log::info('Initiating return process for Order ID: ' . $orderId);

            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ]);
            }

            $returnRequest = OrderReturnRequest::where('order_id', $orderId)->first();

            if (!$returnRequest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Return request not found'
                ]);
            }

            if (!empty($returnRequest->shiprocket_return_order_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Return already processed'
                ]);
            }

            $shiprocketService = new ShiprocketService(new Client());
            $returnResponse = $shiprocketService->createReturnOrder($orderId);
            $returnData = $returnResponse->getData(true);
            Log::channel('shiprocket')->info('Return Order Response', $returnData);

            if (
                !isset($returnData['status']) ||
                $returnData['status'] !== true
            ) {

                $returnRequest->update([
                    'status' => 'failed',
                    'error_response' => $returnData,
                ]);

                Log::channel('shiprocket')->error('Failed to create return order', $returnData);

                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create return order'
                ]);
            }

            $returnRequest->update([

                'status' => 'approved',
                'refund_status' => 'initiated',
                'shiprocket_return_order_id' =>  $returnData['shiprocket_response']['order_id'] ?? null,
                'shiprocket_shipment_id' => $returnData['shiprocket_response']['shipment_id'] ?? null,
                'create_return_payload' => [
                    'order_id' => $orderId
                ],
                'create_return_response' => $returnData,
                'approved_at' => now(),
            ]);

            $shipmentId = $returnData['shiprocket_response']['shipment_id'] ?? null;
            $awbResponse = $shiprocketService->assignCourierAwb($shipmentId);
            $awbData =  $awbResponse->getData(true);

            Log::channel('shiprocket')->info('Return Order AWB Response', $awbData);

            if (
                isset($awbData['status']) &&
                $awbData['status'] === true &&
                isset($awbData['shiprocket_response']['awb_assign_status']) &&
                $awbData['shiprocket_response']['awb_assign_status'] == 1
            ) {

                $awbInfo = $awbData['shiprocket_response']['response']['data'] ?? [];
                $returnRequest->update([
                    'status' => 'awb_assigned',
                    'awb_code' => $awbInfo['awb_code'] ?? '',
                    'courier_name' => $awbInfo['courier_name'] ?? '',
                    'pickup_token_number' => $awbInfo['order_id'] ?? '',
                    'pickup_scheduled_date' => $awbInfo['pickup_scheduled_date'] ?? null,
                    'pickup_status' => 'scheduled',
                    'awb_payload' => [
                        'shipment_id' => $shipmentId
                    ],
                    'awb_response' => $awbData,
                ]);

                Log::channel('shiprocket')->info(
                    'Return order AWB assigned successfully for Order ID: ' . $orderId
                );

                $trackResponse = $shiprocketService->trackReturnShipment($awbInfo['awb_code']);
                $trackData = $trackResponse->getData(true);
                Log::channel('shiprocket')->info('Return Order Tracking Response', $trackData);
            

                if (
                    isset($trackData['status']) &&
                    $trackData['status'] === true
                ) {

                    $tracking = $trackData['shiprocket_response']['tracking_data'] ?? [];
                    $shipmentTrack = $tracking['shipment_track'][0] ?? [];
                    $currentStatus = $shipmentTrack['current_status'] ?? '';

                    $systemStatus = match (strtolower($currentStatus)) {
                        'pickup generated' => 'pickup_generated',
                        'picked up' => 'picked_up',
                        'delivered' => 'received',
                        default => 'processing',
                    };

                    $returnRequest->update([
                        'status' => $systemStatus,
                        'current_tracking_status' => $currentStatus,
                        'pickup_completed_at' =>
                            !empty($shipmentTrack['pickup_date'])
                                ? $shipmentTrack['pickup_date']
                                : null,
                        'tracking_payload' => [
                            'awb_code' => $awbInfo['awb_code']
                        ],
                        'tracking_data' => $trackData,
                    ]);

                } else {
                    $returnRequest->update([
                        'status' => 'tracking_failed',
                        'error_response' => $trackData,
                    ]);

                    Log::channel('shiprocket')->error(
                        'Tracking failed for Order ID: ' . $orderId,
                        $trackData
                    );
                }

            } else {

                $returnRequest->update([
                    'status' => 'awb_failed',
                    'error_response' => $awbData,
                ]);

                Log::channel('shiprocket')->error(
                    'Failed to assign AWB for Order ID: ' . $orderId,
                    $awbData
                );
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Return order created successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($returnRequest)) {
                $returnRequest->update([
                    'status' => 'failed',
                    'error_response' => [
                        'message' => $e->getMessage(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                    ]
                ]);
            }

            Log::channel('shiprocket')->error('RETURN ORDER ERROR', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function cancelReturnRequest($orderId)
    {
        $returnRequest = OrderReturnRequest::where('order_id', $orderId)->first();

        $order = Order::find($orderId);

        if (!$returnRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Return request not found'
            ]);
        }

        if (empty($returnRequest->shiprocket_return_order_id)) {
            $returnRequest->update([
                'status' => 'return request cancelled',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Return request cancelled successfully'
            ]);
        }
        $order->update([
            'status' => 'delivered',
        ]);
        $shiprocketService = new ShiprocketService(new Client());
        $cancelResponse = $shiprocketService->cancelShipmentOrder($returnRequest->shiprocket_return_order_id);
        $cancelData = $cancelResponse->getData(true);
        Log::channel('shiprocket')->info('Cancel Return Order Response', $cancelData);

        if (isset($cancelData['status']) && $cancelData['status'] === true) {
            $returnRequest->update([
                'status' => 'return request cancelled',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Return request cancelled successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Failed to cancel return request in Shiprocket'
            ]);
        }
    }
}
