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

    public function returnDeliveredOrders()
    {
        $returnRequests = OrderReturnRequest::with(['order', 'cart.product', 'cart.color'])
            ->where('status', 'return_delivered')
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return view('backend.order.return_delivered', compact('returnRequests'));
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
        $amount = round($order_data['total_amount']);
        $order_data['total_amount'] = (int)$amount;
        
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
        session()->put('thank_you_order_id', $order->id);
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
            session()->put('order_id', $order->id);
            return redirect()->route('payment')->with(['id'=>$order->id, 'order_id'=>$order->id]);
        }
        else{
            session()->forget('cart');
            session()->forget('coupon');
        }
        Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);
        
        if (strtolower($order->payment_method) == 'cod') {
            $shiprocketService = new ShiprocketService(new Client());
            $shiprocketService->createCompleteShipmentForOrder($order);
        }


        if (request('payment_method') == 'razorpay') {
            $razorpayController = new RazorpayController();
            return $razorpayController->pay($order->id);
        }
        // dd($users);        
        request()->session()->flash('success','Your order placed successfully');
        return redirect()->route('thank.you', ['order_id' => $order->id]);
    }

    public function thankYou(Request $request, $order_id = null)
    {
        $order = null;

        if ($order_id) {
            $order = Order::find($order_id);
        }

        if (!$order && $request->session()->has('thank_you_order_id')) {
            $order = Order::find($request->session()->get('thank_you_order_id'));
        }

        return view('frontend.pages.thankyou', compact('order'));
    }

   
    public function show($id)
    {
        $order=Order::with(['cart.product', 'cart.color', 'returnRequests.cart.product', 'returnRequests.cart.color'])->find($id);
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
            $data['delivered_at'] = $order->delivered_at ?: now();
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
            $orders = Order::select('id', 'order_number', 'status')
                ->where('user_id', auth()->user()->id)
                ->orderBy('id', 'DESC')
                ->get();
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

        $order = Order::with(['returnRequests' => function ($query) {
                $query->latest('id');
            }])
            ->where('user_id', auth()->id())
            ->where('order_number', $request->order_number)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid order number'
            ]);
        }

        $latestReturnRequest = $order->returnRequests->first();

        return response()->json([
            'status' => 'success',
            'order_status' => $order->status,
            'return_request' => $latestReturnRequest ? [
                'type' => $latestReturnRequest->return_type,
                'status' => $latestReturnRequest->status,
                'current_tracking_status' => $latestReturnRequest->current_tracking_status,
                'refund_status' => $latestReturnRequest->refund_status,
            ] : null,
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
        $order = Order::where('id', $id)->where('user_id', auth()->user()->id)->with(['cart.product', 'cart.color', 'user', 'returnRequests.cart.product'])->first();
        $completedStatuses = ['rejected', 'failed', 'refunded', 'return_delivered', 'received', 'completed', 'return_cancelled', 'exchange_rejected', 'exchange_cancelled'];

        $activeReturnRequest = $order
            ? $order->returnRequests()->whereNotIn('status', $completedStatuses)->latest()->first()
            : null;
        $latestReturnRequest = $order ? $order->returnRequests()->latest()->first() : null;

        return view('frontend.pages.order_details', compact('order', 'activeReturnRequest', 'latestReturnRequest'));
    }
    
    public function orderUpdate(Request $request){
        $order = Order::where('id', $request->id)->where('user_id', auth()->user()->id)->first();
        if(!$order){
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ]);
        }

        if($request->status == 'Cancell'){
            return $this->cancelOrder($order->id);
        } else if($request->status == 'Return') {
            return $this->returnOrder($request->id);
        } else if($request->status == 'Return Cancel') {
            return $this->cancelReturnRequest($request->id);
        } else if($request->status == 'Exchange Cancel') {
            return $this->cancelExchangeRequest($request->id);
         } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid status update request'
             ]);
        }
        
    }

    public function cancelOrder($orderId){
        $order = Order::find($orderId);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ]);
        }

        $cancelOrderData = null;
        $shipmentDetail = ShipmentDetails::where('order_id', $orderId)->first();
        if ($shipmentDetail && $shipmentDetail->shipment_order_id) {
            $shiprocketService = new ShiprocketService(new Client());
            $cancelOrder = $shiprocketService->createCancelOrder($shipmentDetail->shipment_order_id);
            $cancelOrderData = $cancelOrder->getData(true);
            Log::info('Cancel Order Response: ', $cancelOrderData);
        }

        if ($cancelOrderData != null && (!isset($cancelOrderData['status']) || $cancelOrderData['status'] !== true)) {
            return response()->json([
                'status' => false,
                'message' => 'Order cancellation failed in Shiprocket'
            ]);
        }

        if(isset($order) && $order->payment_method == 'cod'){
            $order->status = 'cancel';
            $order->payment_status = 'cancelled';
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled successfully'
            ]);
        }
        else if($order->payment_method == 'razorpay' && $order->payment_status == 'paid'){
            $razorpayController = new RazorpayController();
            $refundResponse = $razorpayController->refundPayment($order->id, $order->total_amount);
            $refundResponseData = $refundResponse->getData(true);

            if (isset($refundResponseData['status']) && $refundResponseData['status'] === true) {
                $order->status = 'cancel';
                $order->payment_status = 'refunded';
                $order->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Order cancelled and refund initiated successfully'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Refund failed, order cancellation unsuccessful'
            ]);
        } else if($order->payment_method == 'razorpay' && $order->payment_status != 'paid') {
            $order->status = 'cancel';
            $order->payment_status = 'cancelled';
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled successfully'
            ]);
        }else{
            return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong'
                ]);
        }
    }

    public function returnExchange(Request $request)
    {
        $request->validate([
            'order_id'      => 'required|exists:orders,id',
            'cart_id'       => 'required|exists:carts,id',
            'request_type'  => 'required|in:return,exchange',
            'reason'        => 'required|string',
            'notes'         => 'nullable|string',
            'images'        => 'required|array|min:1',
            'images.*'      => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'customer_upi_id' => 'nullable|string|max:100',
        ], [
            'images.required' => 'Please upload at least one product image.',
            'images.array' => 'Please upload valid product images.',
            'images.min' => 'Please upload at least one product image.',
            'images.*.required' => 'Please upload at least one product image.',
            'images.*.image' => 'Each uploaded file must be an image.',
            'images.*.mimes' => 'Product images must be JPG, PNG, or WEBP files.',
            'images.*.max' => 'Each product image must be 5 MB or smaller.',
        ]);

        $order = Order::where('id', $request->order_id)->with(['cart.product', 'cart.color', 'user', 'returnRequests.cart.product'])->first();
        if (!$order) {
            request()->session()->flash('error', 'Order not found');
            return back()->withInput()->with('return_exchange_modal', true);
        }
        // Prevent placing another exchange request if an exchange has already been approved for this order
        if ($request->request_type === 'exchange') {
            $approvedExchangeExists = OrderReturnRequest::where('order_id', $order->id)
                ->where('return_type', 'exchange')
                ->where('status', 'exchange_approved')
                ->exists();

            if ($approvedExchangeExists) {
                request()->session()->flash('error', 'An exchange has already been approved for this order. You cannot place another exchange request.');
                return back()->withInput()->with('return_exchange_modal', true);
            }
        }
        
        if ($order->payment_method === 'cod' && $request->request_type === 'return') {
            $request->validate([                                        
                'customer_upi_id' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/'],
            ], [
                'customer_upi_id.required' => 'Please enter your UPI ID for COD refund.',
                'customer_upi_id.regex' => 'Please enter a valid UPI ID.',
            ]);
        }

        $cartItem = Cart::where('id', $request->cart_id)
            ->where('order_id', $order->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$cartItem) {
            request()->session()->flash('error', 'Please select a valid product from this order.');
            return back()->withInput()->with('return_exchange_modal', true);
        }

        $completedStatuses = [
            'rejected',
            'failed',
            'refunded',
            'return_delivered',
            'received',
            'completed',
            'return_cancelled',
            'return request cancelled',
            'exchange_rejected',
            'exchange_cancelled',
        ];
        $activeRequest = OrderReturnRequest::where('order_id', $order->id)
            ->whereNotIn('status', $completedStatuses)
            ->first();

        if ($activeRequest) {
            request()->session()->flash('error', 'A return/exchange request is already in progress for this order.');
            return back()->withInput()->with('return_exchange_modal', true);
        }

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

        $returnRequest = OrderReturnRequest::create([
            'order_id'          => $order->id,
            'cart_id'           => $cartItem->id,
            'return_type'       => $request->request_type,
            'reason'            => $request->reason,
            'customer_comment'  => $request->notes,
            'customer_upi_id'   => $order->payment_method === 'cod' && $request->request_type === 'return'
                ? $request->customer_upi_id
                : null,
            'images'            => $uploadedImages,
            'status'            => $request->request_type === 'exchange' ? 'exchange_requested' : 'pending',
        ]);

        $flashType = 'success';
        $flashMessage = 'Your ' . $request->request_type . ' request has been submitted successfully.';

        if($request->request_type == 'return'){
            $returnResponse = $this->returnOrder($order->id, $returnRequest->id);
            $returnData = $returnResponse->getData(true);

            if (empty($returnData['status'])) {
                $flashType = 'error';
                $flashMessage = $returnData['message'] ?? 'Your return request was saved, but return pickup could not be created. Please contact support.';
            }
        }

        $activeReturnRequest = $order
            ? $order->returnRequests()->whereNotIn('status', $completedStatuses)->latest()->first()
            : null;
        $latestReturnRequest = $order ? $order->returnRequests()->latest()->first() : null;

        return redirect()->back()->with([
            'order' => $order,
            'activeReturnRequest' => $activeReturnRequest,
            'latestReturnRequest' => $latestReturnRequest,
            $flashType => $flashMessage,
        ]);
    }

    public function exchangeRequests()
    {
        $exchangeRequests = OrderReturnRequest::with(['order', 'cart.product', 'cart.color'])
            ->where('return_type', 'exchange')
            ->orderBy('id', 'DESC')
            ->paginate(10);
            
        return view('backend.order.exchange_requests', compact('exchangeRequests'));
    }

    public function approveExchangeRequest($id)
    {
        $returnRequest = OrderReturnRequest::where('return_type', 'exchange')->findOrFail($id);

        if (!in_array($returnRequest->status, ['exchange_requested', 'pending'])) {
            session()->flash('error', 'This exchange request has already been processed.');
            return back();
        }

        try {
            $shiprocketService = new ShiprocketService(new Client());
            $response = $shiprocketService->createExchangeOrder($returnRequest);
            $data = $response->getData(true);

            if (empty($data['status'])) {
                $returnRequest->update([
                    'exchange_create_response' => $data,
                    'error_response' => $data,
                ]);

                session()->flash('error', $data['message'] ?? 'Failed to create exchange order with Shiprocket.');
                return back();
            }

            $returnRequest->update([
                'status' => 'exchange_approved',
                'approved_at' => now(),
                'exchange_approved_at' => now(),
                'exchange_order_id' => $data['shiprocket_response']['order_id'] ?? null,
                'exchange_shipment_id' => $data['shiprocket_response']['shipment_id'] ?? null,
                'exchange_create_payload' => $data['payload'] ?? null,
                'exchange_create_response' => $data,
            ]);

            session()->flash('success', 'Exchange request approved and Shiprocket exchange order created.');
            return back();

        } catch (\Exception $e) {
            $returnRequest->update([
                'error_response' => [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ],
            ]);

            session()->flash('error', $e->getMessage());
            return back();
        }
    }

    public function rejectExchangeRequest(Request $request, $id)
    {
        $request->validate([
            'admin_comment' => 'nullable|string|max:1000',
        ]);

        $returnRequest = OrderReturnRequest::where('return_type', 'exchange')->findOrFail($id);

        if ($returnRequest->status !== 'exchange_requested' && $returnRequest->status !== 'pending') {
            request()->session()->flash('error', 'This exchange request has already been processed.');
            return back();
        }

        $returnRequest->update([
            'status' => 'exchange_rejected',
            'admin_comment' => $request->admin_comment,
            'rejected_at' => now(),
        ]);

        request()->session()->flash('success', 'Exchange request rejected successfully.');
        return back();

    }

    private function calculateReturnRefundAmount(OrderReturnRequest $returnRequest): float
    {
        $order = $returnRequest->order;
        if (!$order) {
            return 0.0;
        }

        $cartItems = $order->cart_info;
        $returnItems = $returnRequest->cart_id
            ? $cartItems->where('id', $returnRequest->cart_id)
            : $cartItems;

        if ($returnItems->isEmpty()) {
            return 0.0;
        }

        $orderItemsTotal = $cartItems->sum(function ($item) {
            return (($item->price ?? 0) + ($item->gst_amt ?? 0)) * ($item->quantity ?? 1);
        });

        $returnItemsTotal = $returnItems->sum(function ($item) {
            return (($item->price ?? 0) + ($item->gst_amt ?? 0)) * ($item->quantity ?? 1);
        });

        if ($orderItemsTotal <= 0) {
            return round((float) $returnItems->sum('amount'), 2);
        }

        $ratio = $returnItemsTotal / $orderItemsTotal;
        $couponShare = ((float) ($order->coupon ?? 0)) * $ratio;
        $shippingShare = ((float) ($order->shiping_charges ?? 0)) * $ratio;

        return round(max(0, $returnItemsTotal - $couponShare + $shippingShare), 2);
    }

    public function refundReturnRequest($id)
    {
        $returnRequest = OrderReturnRequest::with(['order.cart_info'])->findOrFail($id);

        if ($returnRequest->status !== 'return_delivered') {
            request()->session()->flash('error', 'Refund is available only after return delivery.');
            return back();
        }

        if (in_array($returnRequest->refund_status, ['processed', 'refunded'])) {
            request()->session()->flash('error', 'Refund has already been processed.');
            return back();
        }

        $order = $returnRequest->order;
        if (!$order) {
            request()->session()->flash('error', 'Order not found for this return request.');
            return back();
        }

        $refundAmount = $this->calculateReturnRefundAmount($returnRequest);

        if ($order->payment_method === 'razorpay') {
            $razorpayController = new RazorpayController();
            $refundResponse = $razorpayController->refundPayment($order->id, $refundAmount);
            $refundData = $refundResponse->getData(true);

            if (!empty($refundData['status'])) {
                $returnRequest->update([
                    'refund_status' => 'processed',
                    'refund_amount' => $refundAmount,
                    'refund_id' => $refundData['data']['id'] ?? null,
                    'refund_payload' => $refundData,
                    'refunded_at' => now(),
                ]);

                $order->payment_status = 'refunded';
                $order->save();

                request()->session()->flash('success', 'Razorpay refund processed successfully.');
                return back();
            }

            request()->session()->flash('error', $refundData['message'] ?? 'Razorpay refund failed.');
            return back();
        }

        if ($order->payment_method === 'cod') {
            request()->session()->flash('error', 'COD refunds must be processed manually using the customer UPI ID.');
            return back();
        }

        request()->session()->flash('error', 'Refund is not available for this payment method.');
        return back();
    }

    public function updateCodRefundStatus(Request $request, $id)
    {
        $request->validate([
            'refund_status' => 'required|in:initiated,processed',
        ]);

        $returnRequest = OrderReturnRequest::with(['order.cart_info'])->findOrFail($id);
        $order = $returnRequest->order;

        if (!$order || $order->payment_method !== 'cod') {
            request()->session()->flash('error', 'Manual refund status is available only for COD orders.');
            return back();
        }

        if ($returnRequest->status !== 'return_delivered') {
            request()->session()->flash('error', 'Refund status can be updated only after return delivery.');
            return back();
        }

        $returnRequest->update([
            'refund_status' => $request->refund_status,
            'refund_amount' => $this->calculateReturnRefundAmount($returnRequest),
            'refunded_at' => $request->refund_status === 'processed' ? now() : null,
        ]);

        if ($request->refund_status === 'processed') {
            $order->payment_status = 'refunded';
            $order->save();
        }

        request()->session()->flash('success', 'COD refund status updated successfully.');
        return back();
    }

    public function returnOrder($orderId, $returnRequestId = null)
    {
        DB::beginTransaction();

        try {
            Log::info('Initiating return process for Order ID: ' . $orderId);
            $order = Order::find($orderId);
            if (!$order) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Order not found'
                ]);
            }
            $returnRequest = $returnRequestId
                ? OrderReturnRequest::where('order_id', $orderId)->find($returnRequestId)
                : OrderReturnRequest::where('order_id', $orderId)->latest()->first();

            if (!$returnRequest) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Return request not found'
                ]);
            }

            if (!empty($returnRequest->shiprocket_return_order_id)) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Return already processed'
                ]);
            }

            $shiprocketService = new ShiprocketService(new Client());
            $returnResponse = $shiprocketService->createReturnOrder($orderId, $returnRequest);
            $returnData = $returnResponse->getData(true);
            Log::channel('shiprocket')->info('Return Order Response', $returnData);

            if (
                !isset($returnData['status']) ||
                $returnData['status'] !== true
            ) {
                DB::rollBack();
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
        $completedStatuses = [
            'rejected',
            'failed',
            'refunded',
            'return_delivered',
            'received',
            'completed',
            'return request cancelled',
        ];
        $returnRequest = OrderReturnRequest::where('order_id', $orderId)
        ->where('return_type', 'return')
        ->whereNotIn('status', $completedStatuses)
        ->latest('id')
        ->first();

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
        // $cancelResponse = $shiprocketService->cancelShipmentOrder($returnRequest->shiprocket_return_order_id);
        $cancelResponse = $shiprocketService->cancelShipmentOrder($returnRequest);
        if (!is_array($cancelResponse)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid response from Shiprocket'
            ]);
        }
        $cancelData = $cancelResponse;
        // $cancelData = $cancelResponse->getData(true);
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

    public function cancelExchangeRequest($orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ]);
        }

        $exchangeRequest = OrderReturnRequest::where('order_id', $order->id)
            ->where('return_type', 'exchange')
            ->latest()
            ->first();

        if (!$exchangeRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Exchange request not found'
            ]);
        }

        $blockedStatuses = [
            'exchange_pickup_generated',
            'exchange_picked_up',
            'exchange_in_transit',
            'exchange_return_delivered',
            'exchange_qc_passed',
            'exchange_qc_failed',
            'exchange_out_for_delivery',
            'exchange_delivered',
            'exchange_cancelled',
        ];

        if (in_array($exchangeRequest->status, $blockedStatuses)) {
            return response()->json([
                'status' => false,
                'message' => 'Exchange request cannot be cancelled after pickup is generated.'
            ]);
        }

        if (!in_array($exchangeRequest->status, ['pending', 'exchange_requested', 'exchange_approved'])) {
            return response()->json([
                'status' => false,
                'message' => 'Exchange request cannot be cancelled at this stage.'
            ]);
        }

        $shiprocketCancelData = null;

        if ($exchangeRequest->exchange_order_id || $exchangeRequest->awb_code) {
            $shiprocketService = new ShiprocketService(new Client());
            $shiprocketCancelData = $shiprocketService->cancelShipmentOrder($exchangeRequest);

            if (
                is_array($shiprocketCancelData) &&
                isset($shiprocketCancelData['status']) &&
                $shiprocketCancelData['status'] === false
            ) {
                $exchangeRequest->update([
                    'error_response' => $shiprocketCancelData,
                ]);

                return response()->json([
                    'status' => false,
                    'message' => $shiprocketCancelData['message'] ?? 'Failed to cancel exchange order in Shiprocket.'
                ]);
            }
        }

        $exchangeRequest->update([
            'status' => 'exchange_cancelled',
            'error_response' => null,
            'tracking_payload' => $shiprocketCancelData,
        ]);

        $order->update([
            'status' => 'delivered',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Exchange request cancelled successfully'
        ]);
    }
}
