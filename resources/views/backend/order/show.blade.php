@extends('backend.layouts.master')

@section('title','Order Detail')

@section('main-content')
<div class="card">
<h5 class="card-header">Order       
  {{-- <a href="{{route('order.pdf',$order->id)}}" class=" btn btn-sm btn-primary shadow-sm float-right"><i class="fas fa-download fa-sm text-white-50"></i> Generate PDF</a> --}}
  @if($shipmentDetails)
    <a href="{{ $shipmentDetails->label_pdf }}" class="btn btn-sm btn-primary">Download LABEL PDF</a>
    <a href="{{ $shipmentDetails->manifest_url }}" class="btn btn-sm btn-primary">Download Menifeast PDF</a>
  @endif
  </h5>
  <div class="card-body">
    @if($order)
    <table class="table table-striped table-hover">
      <thead>
        <tr>
            <th>S.N.</th>
            <th>Order No.</th>
            <th>Name</th>
            <th>Email</th>
            <th>Quantity</th>
            <th>Charge</th>
            <th>Total Amount</th>
            <th>Status</th>
            {{-- <th>Action</th> --}}
        </tr>
      </thead>
      <tbody>
        <tr>
            <td>{{$order->id}}</td>
            <td>{{$order->order_number}}</td>
            <td>{{$order->first_name}} {{$order->last_name}}</td>
            <td>{{$order->email}}</td>
            <td>{{$order->quantity}}</td>
            <td>₹{{$order->shiping_charges ?? ''}}</td>
            <td>₹{{number_format($order->total_amount,2)}}</td>
            <td>
                @if($order->status=='new')
                  <span class="badge badge-primary">{{$order->status}}</span>
                @elseif($order->status=='process')
                  <span class="badge badge-warning">{{$order->status}}</span>
                @elseif($order->status=='delivered')
                  <span class="badge badge-success">{{$order->status}}</span>
                @else
                  <span class="badge badge-danger">{{$order->status}}</span>
                @endif
            </td>
            {{-- <td>
                <a href="{{route('order.edit',$order->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="edit" data-placement="bottom"><i class="fas fa-edit"></i></a>
                <form method="POST" action="{{route('order.destroy',[$order->id])}}">
                  @csrf
                  @method('delete')
                      <button class="btn btn-danger btn-sm dltBtn" data-id={{$order->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-trash-alt"></i></button>
                </form>
            </td> --}}

        </tr>
      </tbody>
    </table>

    <section class="confirmation_part section_padding">
      <div class="order_boxes">
        <div class="row">
          <div class="col-lg-6 col-lx-4">
            <div class="order-info">
              <h4 class="text-center pb-4">ORDER INFORMATION</h4>
              <table class="table">
                    <tr class="">
                        <td>Order Number</td>
                        <td> : {{$order->order_number}}</td>
                    </tr>
                    <tr>
                        <td>Order Date</td>
                        <td> : {{$order->created_at->format('D d M, Y')}} at {{$order->created_at->format('g : i a')}} </td>
                    </tr>
                    <tr>
                        <td>Quantity</td>
                        <td> : {{$order->quantity}}</td>
                    </tr>
                    <tr>
                        <td>Order Status</td>
                        <td> : {{ucfirst($order->status)}}</td>
                    </tr>
                    <tr>
                        <td>Shipping Charge</td>
                        <td> : ₹ {{$order->shiping_charges}}</td>
                    </tr>
                    <tr>
                        <td>Shipping Charge</td>
                        <td> : ₹ {{$order->total_gst_amount ?? ''}}</td>
                    </tr>
                    @if(isset($order->coupon) && $order->coupon != null)
                    <tr>
                      <td>Coupon</td>
                      <td> : ₹ {{number_format($order->coupon,2)}}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Total Amount</td>
                        <td> : ₹ {{number_format($order->total_amount,2)}}</td>
                    </tr>
                    <tr>
                        <td>Payment Method</td>
                        <td> : @if($order->payment_method=='cod') Cash on Delivery @else Prepaid @endif</td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td> : {{ucfirst($order->payment_status)}}</td>
                    </tr>
              </table>
            </div>
          </div>

          <div class="col-lg-6 col-lx-4">
            <div class="shipping-info">
              <h4 class="text-center pb-4">SHIPPING INFORMATION</h4>
              <table class="table">
                    <tr class="">
                        <td>Full Name</td>
                        <td> : {{$order->first_name}} {{$order->last_name}}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td> : {{$order->email}}</td>
                    </tr>
                    <tr>
                        <td>Phone No.</td>
                        <td> : {{$order->phone}}</td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td> : {{$order->address1}}, {{$order->address2}}</td>
                    </tr>
                    <tr>
                        <td>Country</td>
                        <td> : {{$order->country}}</td>
                    </tr>
                    <tr>
                        <td>Post Code</td>
                        <td> : {{$order->post_code}}</td>
                    </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>

    @if($order->returnRequests && $order->returnRequests->count())
    <section class="confirmation_part section_padding mt-4">
        <div class="order_boxes">
            <div class="row">
                <div class="col-lg-12">
                    <div class="order-info">
                        <h4 class="text-center pb-4">RETURN / EXCHANGE REQUESTS</h4>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Product</th>
                                    <th>Reason</th>
                                    <th>Images</th>
                                    <th>Status</th>
                                    <th>Admin Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->returnRequests as $request)
                                <tr>
                                    <td>{{ ucfirst($request->return_type) }}</td>
                                    <td>
                                        @if($request->cart)
                                            {{ $request->cart->product->title ?? 'N/A' }}<br>
                                            <small>Qty: {{ $request->cart->quantity }}</small>
                                        @else
                                            Full order
                                        @endif
                                    </td>
                                    <td>
                                        {{ $request->reason }}
                                        @if($request->customer_comment)
                                            <br><small>Note: {{ $request->customer_comment }}</small>
                                        @endif
                                        @if($request->admin_comment)
                                            <br><small>Admin: {{ $request->admin_comment }}</small>
                                        @endif
                                        @if($order->payment_method == 'cod' && $request->return_type == 'return' && $request->customer_upi_id)
                                            <br><small><strong>UPI ID:</strong> {{ $request->customer_upi_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if(is_array($request->images) && count($request->images))
                                            @foreach($request->images as $image)
                                                <a href="{{ asset('public/'.$image) }}" target="_blank">
                                                    <img src="{{ asset('public/'.$image) }}" width="55" height="55" style="object-fit:cover; border-radius:6px; margin:2px;" alt="Request image">
                                                </a>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ ucwords(str_replace('_', ' ', $request->status)) }}</td>
                                    <td style="min-width:220px;">
                                        @if($request->return_type === 'exchange' && $request->status === 'pending')
                                            <form method="POST" action="{{ route('exchange-request.approve', $request->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success mb-2">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('exchange-request.reject', $request->id) }}">
                                                @csrf
                                                <textarea name="admin_comment" rows="2" class="form-control mb-2" placeholder="Reject reason (optional)"></textarea>
                                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
    
    {{-- PRODUCT INFORMATION --}}
    <section class="confirmation_part section_padding mt-4">
        <div class="order_boxes">
            <div class="row">
                <div class="col-lg-12">
                    <div class="order-info">
                        <h4 class="text-center pb-4">PRODUCT INFORMATION</h4>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.N</th>
                                    <th>Product Name</th>
                                    <th>Product Image</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->cart as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->product->title ?? 'N/A' }}</td>
                                    @php 
										$photo = explode(',',$item->product['photo']);
                                        $images = [];
                                        if(isset($item->color_id) && $item->color_id != null){
                                            $color = \App\Models\Color::find($item->color_id);
                                            $images = $color->images->pluck('image')->map(function($image) {
                                                return asset('public/storage/products/'.$image);
                                            });
                                        }
                                        if(isset($images) && is_countable($images) && count($images)){
                                            $item->color_img = $images[0] ?? null;
                                        }
									@endphp
                                    @if(isset($item->color_img) && $item->color_img != null) 
                                        <td><img class="image" width="130" height="150" src="{{$item->color_img}}" alt="{{ $item->color_img }}"></td>
                                    @else
                                        <td><img class="image" width="130" height="150" src="{{asset('public/'.$photo[0])}}" alt="{{asset('public/'.$photo[0])}}"></td>
                                    @endif
                                    <td>{{ $item->color->color_name ?? 'N/A' }}</td>
                                    @php
                                        $price = json_decode($item->size_price,true) ?? [];
                                    @endphp
                                    <td><span>{{ $price['size']}}</span></td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₹{{ number_format($item->price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

  </div>
</div>
@endsection

@push('styles')
<style>
    .order-info,.shipping-info{
        background:#ECECEC;
        padding:20px;
    }
    .order-info h4,.shipping-info h4{
        text-decoration: underline;
    }

</style>
@endpush
