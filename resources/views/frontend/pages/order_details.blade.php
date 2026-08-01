@extends('frontend.layouts.master')
@section('title','Aashi-Ecommerce || Order Details')
@section('main-content')

<!-- Breadcrumbs -->   
<div class="breadcrumbs">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="bread-inner">
                    <ul class="bread-list">
                        <li><a href="{{ route('home') }}">Home<i class="ti-arrow-right"></i></a></li>
                        <li><a href="{{ route('myorders') }}">My Orders<i class="ti-arrow-right"></i></a></li>
                        <li class="active"><a href="#">Order Details</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Section -->
<div class="order-details-section section">
    <div class="container">

        <!-- Order Info -->
        <div class="card mb-4 p-3 shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h4>Order #{{$order->order_number ?? '-'}}</h4>
                    <small>Order Date: {{isset($order->created_at) && $order->created_at != '' ? date('d-m-Y', strtotime($order->created_at)) : '-'}}</small>
                </div>
                <div class="d-flex flex-column">
                    <h6>Order Status</h6>
                    <h6><span class="badge badge-info">@if(isset($order->status)) {{strtoupper($order->status)}} @else {{''}} @endif</span></h6>
                </div>
                <div class="d-flex flex-column">
                    <h6>Payment Status</h6>
                    <h6><span class="badge badge-dark">@if(isset($order->payment_status)) {{strtoupper($order->payment_status) ?? '-'}} @else {{''}} @endif</span></h6>
                </div>

                <div class="d-flex flex-column">
                     @php
                        use Carbon\Carbon;
                        use App\Models\OrderReturnRequest;
                        $deliveredDate = null;
                        if (isset($order->delivered_at) && $order->delivered_at) {
                            $deliveredDate = Carbon::parse($order->delivered_at);
                        } elseif (isset($order->status) && $order->status === 'delivered' && isset($order->updated_at)) {
                            $deliveredDate = Carbon::parse($order->updated_at);
                        }
                        $returnExpiryDate = $deliveredDate ? $deliveredDate->copy()->addDays(7) : null;
                        $countReturnReqtuest = OrderReturnRequest::where('order_id', $order->id)->count();
                        $exchangeCancelableStatuses = ['pending', 'exchange_requested'];
                        $canCancelExchangeRequest = isset($latestReturnRequest)
                            && $latestReturnRequest
                            && $latestReturnRequest->return_type === 'exchange'
                            && in_array($latestReturnRequest->status, $exchangeCancelableStatuses);
                        $returnCancelableStatuses = ['pending', 'approved', 'awb_assigned', 'pickup_generated', 'processing', 'tracking_failed', 'awb_failed'];
                        $canCancelReturnRequest = isset($latestReturnRequest)
                            && $latestReturnRequest
                            && $latestReturnRequest->return_type === 'return'
                            && in_array($latestReturnRequest->status, $returnCancelableStatuses);
                    @endphp

                    @if(isset($latestReturnRequest) && $latestReturnRequest)
                        <h6>{{ ucfirst($latestReturnRequest->return_type) }} request status </h6>
                        <h6><span class="badge badge-info">{{ strtoupper(str_replace('_', ' ', $latestReturnRequest->status)) }}</span></h6>
                        @if($order->payment_method == 'cod' && $latestReturnRequest->return_type == 'return' && $latestReturnRequest->customer_upi_id)
                            <small class="d-block mt-1">Refund UPI ID: <strong>{{ $latestReturnRequest->customer_upi_id }}</strong></small>
                        @endif
                    @endif

                    @php
                        $paymentStatus = strtolower($order->payment_status ?? '');
                        $paymentMethod = strtolower($order->payment_method ?? '');
                    @endphp

                    @if(!empty($paymentMethod) && isset($order->status) && in_array($order->status, ['process', 'new']) &&
                        (
                            ($order->payment_method != 'cod' && $paymentStatus == 'paid') ||
                            ($order->payment_method == 'cod' && $paymentStatus == 'unpaid')
                        )
                    )
                        <button class="btn btn-danger" type="button" onclick="Updateorder({{$order->id}}, 'Cancell', this)">Cancel Order</button>
                    @elseif($order->status == 'delivered' && $countReturnReqtuest == 0)
                        @if($returnExpiryDate && now()->lessThanOrEqualTo($returnExpiryDate))
                            @if(isset($activeReturnRequest) && $activeReturnRequest)
                                <button class="btn btn-secondary" type="button" disabled>
                                    Request In Progress
                                </button>
                            @else
                                <button class="btn btn-danger" type="button"
                                    onclick="Updateorder({{$order->id}}, 'Return', this)">
                                    Request Return / Exchange
                                </button>
                            @endif

                            <small class="text-muted mt-1">
                                Return available till {{ $returnExpiryDate->format('d M Y') }}
                            </small>

                        @else

                            <button class="btn btn-secondary" type="button" disabled>
                                Return Period Expired
                            </button>

                        @endif
                    @elseif($canCancelExchangeRequest)
                        <button class="btn btn-secondary mt-3" type="button" onclick="Updateorder({{$order->id}}, 'Exchange Cancel', this)" >
                            Cancel Exchange Request
                        </button>
                    @elseif($canCancelReturnRequest)
                        <button class="btn btn-secondary mt-3" type="button" onclick="Updateorder({{$order->id}}, 'Return Cancel', this)" >
                            Cancel Return Request
                        </button>
                    @endif
                </div>

                @if(isset($order->invoice_url) && $order->invoice_url != null)
                <div class="d-flex flex-column">
                    <a class="badge badge-info" target="_blank" href="{{$order->invoice_url}}">
                        <h6>Download Invoice</h6>
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Products List -->
        <div class="card mb-4 p-3 shadow-sm">
            <h5 class="mb-3">Products in this order</h5>
            <div class="row">
                <!-- Product Item -->
                @if (isset($order->cart) && is_countable($order->cart) && count($order->cart) > 0)
                @foreach($order->cart as $key => $item)
                    <div class="col-12 col-md-6 mb-4">
                        <div class="ratting_cards">
                            {{-- <img src="/images/products/shirt.png" alt="Men's Shirt" width="80" class="rounded"> --}}
                            @php 
                                $canReview = false;
                                $hasReviewed = false;
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
                                $price = json_decode($item->size_price,true) ?? [];
                                if($item->order && isset($item->order->status) && strtolower($item->order->status) === 'delivered'){
                                    $hasReviewed = \App\Models\ProductReview::where(['product_id' => $item->product_id, 'order_id' => $item->order_id, 'user_id' => $item->user_id])->exists();
                                    if($hasReviewed == false){
                                        $canReview = true; 
                                    }
                                }

                            @endphp
                            
        

                            <div>
                              <div class="rating_card">
                                 <div>
                                 @if(isset($item->color_img) && $item->color_img != null) 
                                    <td><a href="{{route('product-detail',$item->product['slug'])}}" target="_blank"><img width="80" class="rounded" src="{{$item->color_img}}" alt="{{ $item->color_img }}"></a></td>
                                @else
                                    <td><a href="{{route('product-detail',$item->product['slug'])}}" target="_blank"><img width="80" class="rounded" src="{{asset('public/'.$photo[0])}}" alt="{{asset('public/'.$photo[0])}}"></a></td>
                                @endif
                               </div>
                               <div>
                                    <h6 class="mb-1">{{ $item->product->title ?? 'N/A' }}</h6>
                                <p class="mb-0">Quantity: {{ $item->quantity }}</p>
                                <p class="mb-0">Size: <span>{{ $price['size']}}</span></p>
                                <p class="mb-0">Color: <span>{{ $item->color->color_name ?? 'N/A' }}</span></p>
                                <p class="mb-0">Price: ₹<span>{{ number_format($item->price, 2) }}</span></p>
                               </div>
                              </div>
                                {{-- @if($canReview == true)
                                    <a href=""><p class="mb-0">Rate this Product</p></a>
                                @elseif($hasReviewed == true)
                                    <a href=""><p class="mb-0">Your Review</p></a>
                                @endif --}}
                                {{-- REVIEW SECTION --}}
                                @if($item->order && isset($item->order->status) && strtolower($item->order->status) === 'delivered')
                                    {{-- REVIEW SECTION --}}
                                    @if($canReview == true && $hasReviewed == false)

                                    <div class="comment-review mt-3">
                                        <div class="add-review">
                                            <h6>Add A Review</h6>
                                        </div>
                                        @auth
                                        <form class="form"
                                            method="POST"
                                            action="{{ route('review.store', $item->product->slug) }}">
                                            @csrf
                                            <input type="hidden" name="order_id" value="{{ $item->order_id }}">
                                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                            <div class="rating_box mb-2">
                                                <div class="star-rating">
                                                    <div class="star-rating__wrap">
                                                        @for($i=5; $i>=1; $i--)
                                                            <input class="star-rating__input"
                                                                id="star-rating-{{$i}}-{{$item->id}}"
                                                                type="radio"
                                                                name="rate"
                                                                value="{{$i}}">
                                                            <label class="star-rating__ico fa fa-star-o"
                                                                for="star-rating-{{$i}}-{{$item->id}}"
                                                                title="{{$i}} out of 5 stars"></label>
                                                        @endfor
                                                    </div>
                                                </div>
                                                @error('rate')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group mb-2">
                                                <label>Write a review</label>
                                                <textarea name="review"
                                                        rows="3"
                                                        class="form-control"
                                                        ></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-sm btn-primary">
                                                Submit Review
                                            </button>
                                        </form>
                                        @else
                                            <p class="text-center p-2">
                                                You need to
                                                <a href="{{ route('login.form') }}">Login</a>
                                                or
                                                <a href="{{ route('register.form') }}">Register</a>
                                            </p>
                                        @endauth
                                    </div>

                                 
                                    @elseif($hasReviewed == true)
                                        @php
                                            $review = \App\Models\ProductReview::where([
                                                'product_id' => $item->product_id,
                                                'order_id'   => $item->order_id,
                                                'user_id'    => $item->user_id
                                            ])->first();
                                        @endphp
                                        <div class="mt-3 p-2 border rounded bg-light">
                                            <strong>Your Review</strong>
                                            <p class="mb-1">
                                                Rating:
                                                @for($i=1; $i<=5; $i++)
                                                    <i class="fa {{ $review->rate >= $i ? 'fa-star' : 'fa-star-o' }}"></i>
                                                @endfor
                                            </p>
                                            <p class="mb-2">{{ $review->review }}</p>
                                            <!--{{-- EDIT BUTTON --}}-->
                                            <a class="text-primary fw-bold"
                                            data-toggle="collapse"
                                            href="#editReview{{$review->id}}">
                                                ✏️ Edit Review
                                            </a>
                                            <!--{{-- EDIT FORM --}}-->
                                            <div class="collapse mt-2" id="editReview{{$review->id}}">
                                                <form method="POST"
                                                    action="{{ route('review.update', $review->id) }}">
                                                    @csrf
 
                                                    <div class="rating_box mb-2">
                                                        <div class="star-rating">
                                                            <div class="star-rating__wrap">
                                                                @for($i=5; $i>=1; $i--)
                                                                    <input class="star-rating__input"
                                                                        id="edit-star-{{$i}}-{{$item->id}}"
                                                                        type="radio"
                                                                        name="rate"
                                                                        value="{{$i}}"
                                                                        {{ $review->rate == $i ? 'checked' : '' }}>
                                                                    <label class="star-rating__ico fa fa-star-o"
                                                                        for="edit-star-{{$i}}-{{$item->id}}"></label>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                    </div>
 
                                                    <div class="form-group mb-2">
                                                        <textarea name="review"
                                                                rows="3"
                                                                class="form-control"
                                                                >{{ $review->review }}</textarea>
                                                    </div>
 
                                                    <button class="btn btn-sm btn-success">
                                                        Update Review
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
 
                                    
                                @endif
                            
                            </div>
                        </div>
                    </div>
                @endforeach
                @endif
            </div>
        </div>

        <!-- Order Summary -->
        <div class="card mb-4 p-3 shadow-sm">
            <h5>Order Information</h5>
            <table class="table table-borderless mt-3">
                <tbody>
                    <tr>
                        <th>Order Number:</th>
                        <td>{{$order->order_number}}</td>
                    </tr>
                    <tr>
                        <th>Quantity:</th>
                        <td>{{$order->quantity}}</td>
                    </tr>
                    <tr>
                        <th>Shipping Charge:</th>
                        <td>₹ {{number_format($order->shiping_charges ?? $order->shiping_charges,2)}}</td>
                    </tr>
                    <tr>
                        <th>GST Charge:</th>
                        <td>₹ {{number_format($order->total_gst_amount ?? $order->total_gst_amount,2)}}</td>
                    </tr>
                    <tr class="fw-bold">
                        <th>Total Amount:</th>
                        <td>₹ {{number_format($order->total_amount,2)}}</td>
                    </tr>
                    <tr class="fw-bold">
                        <th>Payment Method:</th>
                        <td>@if($order->payment_method=='cod') Cash on Delivery @else Online Payment @endif</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Customer Details -->
        <div class="card mb-4 p-3 shadow-sm">
            <h5 class="mb-3">Shipping Details</h5>
            <p><strong>Full Name:</strong> {{$order->first_name}} {{$order->last_name}}</p>
            <p><strong>Email:</strong> {{$order->email}}</p>
            <p><strong>Phone No.:</strong> {{$order->phone}}</p>
            <p><strong>Address:</strong> {{$order->address1}}, {{$order->address2}}</p>
            <p><strong>City:</strong> {{$order->city}}</p>
            <p><strong>Post Code:</strong> {{$order->post_code}}</p>
            <p><strong>State:</strong> {{$order->state}}</p>
            <p><strong>Country:</strong> {{$order->country}}</p>
        </div>

        <!-- Back Button -->
        <div class="text-end">
            <a href="{{ route('myorders') }}" class="back_orders">Back to Orders</a>
        </div>
    </div>

    {{-- create a modal flow for return/exchange --}}
       
        <!-- Return / Exchange Modal -->
        <div class="modal fade" id="returnExchangeModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:700px; margin:30px auto;">
                <div class="modal-content" style="border:none; border-radius:20px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.2);">

                    <!-- ====== HEADER ====== -->
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; padding:26px 32px 20px; border-bottom:1px solid #f0f0f0;">
                        <div>
                            <p style="margin:0 0 4px 0; font-size:11px; font-weight:600; color:#999; letter-spacing:0.1em; text-transform:uppercase;">
                                Order #{{$order->order_number}}
                            </p>
                            <h5 style="margin:0 0 5px 0; font-size:22px; font-weight:700; color:#1a1a1a;">
                                Request Return / Exchange
                            </h5>
                            <p style="margin:0; font-size:13px; color:#999;">
                                Submit your request with reason and product images
                            </p>
                        </div>
                        <button type="button" data-dismiss="modal" aria-label="Close"
                            style="width:38px; height:38px; border-radius:50%; border:1px solid #e8e8e8; background:#f7f7f7; font-size:20px; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#555; padding:0; margin:0;">
                            &times;
                        </button>
                    </div>

                    <!-- ====== BODY ====== -->
                    <div style="padding:28px 32px; max-height:68vh; overflow-y:auto;">
                        <form id="returnExchangeForm" method="POST" action="{{ route('return.exchange') }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="order_id" id="return_order_id" value="{{$order->id}}">

                            @if ($errors->any())
                                <div class="alert alert-danger" style="margin-bottom:18px; border-radius:8px; text-align:left;">
                                    <strong>Please fix the following:</strong>
                                    <ul style="margin:8px 0 0 18px; padding:0;">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('return_exchange_modal') && session('error'))
                                <div class="alert alert-danger" style="margin-bottom:18px; border-radius:8px; text-align:left;">
                                    {{ session('error') }}
                                </div>
                            @endif
                            
                            @php
                                $paymentsMethod  =  $order->payment_method 
                            @endphp
                            
                            @if($paymentsMethod == 'cod')
                                <!-- Request Type -->
                                <div style="margin-bottom:22px; clear:both;">
                                    <label style="display:block; font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:8px;">
                                        UPI ID <span style="color:#e53935;">*</span>
                                    </label>
                                    <input type="text" name="customer_upi_id" id="customer_upi_id"
                                        placeholder="Enter your UPI ID for refund"
                                        style="width:100%; padding:12px 16px; border-radius:10px; border:1px solid #e0e0e0; background:#fff; font-size:14px; color:#333; cursor:pointer; outline:none; display:block;">
                                    @if ($errors->has('customer_upi_id'))
                                        <span class="text-danger">{{ $errors->first('customer_upi_id') }}</span>
                                    @endif
                                </div>
                                
                            @endif

                            <!-- Request Type -->
                            <div style="margin-bottom:22px; clear:both;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:8px;">
                                    Request Type <span style="color:#e53935;">*</span>
                                </label>
                                <div class="return-type-options">
                                    <label class="return-type-option">
                                        <input type="radio" name="request_type" value="return" required {{ old('request_type', 'return') === 'return' ? 'checked' : '' }}>
                                        <span>Return</span>
                                    </label>
                                    <label class="return-type-option">
                                        <input type="radio" name="request_type" value="exchange" required {{ old('request_type') === 'exchange' ? 'checked' : '' }}>
                                        <span>Exchange</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Product Selection -->
                            <div style="margin-bottom:22px; clear:both;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:8px;">
                                    Select Product <span style="color:#e53935;">*</span>
                                </label>
                                <div class="return-product-list">
                                    @foreach($order->cart as $item)
                                        @php
                                            $modalPhoto = explode(',', $item->product['photo'] ?? '');
                                            $modalImages = [];
                                            if(isset($item->color_id) && $item->color_id != null){
                                                $modalColor = \App\Models\Color::find($item->color_id);
                                                $modalImages = $modalColor ? $modalColor->images->pluck('image')->map(function($image) {
                                                    return asset('public/storage/products/'.$image);
                                                }) : [];
                                            }
                                            $modalImage = (isset($modalImages) && is_countable($modalImages) && count($modalImages))
                                                ? ($modalImages[0] ?? null)
                                                : (!empty($modalPhoto[0]) ? asset('public/'.$modalPhoto[0]) : '');
                                            $modalPrice = json_decode($item->size_price,true) ?? [];
                                        @endphp
                                        <label class="return-product-option">
                                            <input type="radio" name="cart_id" value="{{ $item->id }}" required {{ (string) old('cart_id') === (string) $item->id ? 'checked' : '' }}>
                                            <img src="{{ $modalImage }}" alt="{{ $item->product->title ?? 'Product' }}">
                                            <span>
                                                <strong>{{ $item->product->title ?? 'N/A' }}</strong>
                                                <small>Qty: {{ $item->quantity }} | Size: {{ $modalPrice['size'] ?? 'N/A' }} | Color: {{ $item->color->color_name ?? 'N/A' }}</small>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Reason -->
                            <div id="exchangeNote" style="display:none; margin-bottom:15px; padding:10px; background-color:#fff3cd; color:#856404; border:1px solid #ffeeba; border-radius:5px; font-size:13px;">
                                <strong>Note:</strong> Only defective or damaged items are eligible for an exchange.
                            </div>
                            <div style="margin-bottom:22px; clear:both;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:8px;">
                                    Reason <span style="color:#e53935;">*</span>
                                </label>
                                <select name="reason" id="returnReason" class="wide" required
                                    style="width:100%; padding:14px 16px; border-radius:10px; border:1px solid #e0e0e0; background:#fff; font-size:14px; color:#333; outline:none; font-family:inherit; box-sizing:border-box; display:block;">
                                    <option value="" disabled selected>Select a reason...</option>
                                    <option value="Size/Fit Issue" {{ old('reason') === 'Size/Fit Issue' ? 'selected' : '' }}>Size/Fit Issue</option>
                                    <option value="Wrong Item Received" {{ old('reason') === 'Wrong Item Received' ? 'selected' : '' }}>Wrong Item Received</option>
                                    <option value="Color/Style differs from website" {{ old('reason') === 'Color/Style differs from website' ? 'selected' : '' }}>Color/Style differs from website</option>
                                    <option value="Defective/Damaged" {{ old('reason') === 'Defective/Damaged' ? 'selected' : '' }}>Defective/Damaged</option>
                                    <option value="Other" {{ old('reason') === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <!-- Upload -->
                            <div style="margin-bottom:22px;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:8px;">
                                    Upload Product Images
                                    <span style="color:#e53935;">*</span>
                                </label>
                                <div style="position:relative; border:2px dashed #5db844; border-radius:14px; background:#f6fdf3; padding:40px 20px; text-align:center; cursor:pointer;">
                                    <input type="file" name="images[]" id="returnImages" multiple accept=".jpg,.jpeg,.png,.webp" required
                                        style="position:absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer;">
                                    <div>
                                        <i class="fa fa-cloud-upload" style="font-size:44px; color:#5db844; display:block; margin-bottom:12px;"></i>
                                        <p style="margin:0 0 4px; font-size:15px; font-weight:600; color:#1a1a1a;">Drag &amp; Drop or Click to Upload</p>
                                        <small style="color:#999; font-size:12px;">JPG, PNG, WEBP up to 5 MB each</small>
                                    </div>
                                </div>
                                <!-- Preview -->
                                <div id="imagePreviewContainer" style="display:flex; flex-wrap:wrap; gap:10px; margin-top:12px;"></div>
                            </div>

                            <!-- Additional Notes -->
                            <div style="margin-bottom:8px;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:8px;">
                                    Additional Notes
                                </label>
                                <textarea name="notes" rows="3"
                                    placeholder="Optional notes..."
                                    style="width:100%; padding:14px 16px; border-radius:10px; border:1px solid #e0e0e0; background:#fff; font-size:14px; color:#333; resize:vertical; outline:none; font-family:inherit; box-sizing:border-box;"></textarea>
                            </div>

                        </form>
                    </div>

                    <!-- ====== FOOTER ====== -->
                    <div style="display:flex; justify-content:flex-end; align-items:center; gap:12px; padding:18px 32px 26px; border-top:1px solid #f0f0f0;">
                        <button type="button" data-dismiss="modal"
                            style="padding:12px 28px; border-radius:10px; border:1px solid #e0e0e0; background:#f5f5f5; font-size:14px; font-weight:500; color:#555; cursor:pointer; letter-spacing:0.02em;">
                            Cancel
                        </button>
                        <button type="submit" form="returnExchangeForm" id="submitReturnExchangeButton"
                            style="padding:12px 28px; border-radius:10px; border:none; background:#5db844; color:#fff; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px; letter-spacing:0.02em;">
                            <i class="fa fa-paper-plane" style="font-size:13px;"></i>
                            Submit Request
                        </button>
                    </div>

                </div>
            </div>
        </div>
</div>

<style>

    .back_orders {
        background: #5db844;
        padding: 12px 20px;
        color: white !important;
        border-radius: 5px;
    }

    .ratting_cards {
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 0 10px #ddd;
        height:100%;
        /*display:flex;*/
    }

    /* Cards & Shadow */
    /*.card { border-radius: 12px; background: #fff; transition: 0.3s; }*/
    /*.card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); }*/

    /* Badges */
    .badge-success { background-color: #5db845; color: white; padding: 5px 12px; border-radius: 12px; }
    .badge-info { background-color: #5db844; color: white; padding: 5px 12px; border-radius: 12px;margin-top:8px; }

    /* Buttons */
    .btn-primary { background-color: #007bff; color: white; border: none; padding: 8px 20px; border-radius: 8px; transition: 0.3s; }
    .btn-primary:hover { background-color: #0056b3; }

    /* Product Cards */
    .border { border: 1px solid #e0e0e0 !important; }
    .rounded { border-radius: 10px !important; }

    /* Text */
    h5 { font-weight: 600; }
    h6 { font-weight: 500; }


    .rating_card {
        display: flex;
        gap: 25px;
    }

    .rating_card img
    {
    width:110px
    height:110px;
    object-fit: contain;
    }

    .form-control:focus
    {
        box-shadow:unset;
        border:1px solid #5db844;
    }

    .return-type-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .return-type-option,
    .return-product-option {
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px;
        cursor: pointer;
        margin: 0;
    }

    .return-type-option span {
        font-weight: 600;
        color: #1a1a1a;
    }

    .return-product-list {
        display: grid;
        gap: 10px;
    }

    .return-product-option img {
        width: 64px;
        height: 64px;
        border-radius: 8px;
        object-fit: contain;
        border: 1px solid #eee;
        background: #fff;
        flex-shrink: 0;
    }

    .return-product-option span {
        display: flex;
        flex-direction: column;
        gap: 3px;
        line-height: 1.35;
    }

    .return-product-option small {
        color: #777;
    }

    .return-type-option:has(input:checked),
    .return-product-option:has(input:checked) {
        border-color: #5db844;
        background: #f6fdf3;
    }

    .btn-loading {
        opacity: 0.8;
        pointer-events: none;
    }

</style>    

@endsection

@push('styles')
	<style>

    

		.shop.single .product-des .color ul li a .selected {
			opacity: 1;
			visibility: visible;
		}
		/* Rating */
		.rating_box {
		display: inline-flex;
		}

		.star-rating {
		font-size: 0;
		padding-left: 10px;
		padding-right: 10px;
		}

		.star-rating__wrap {
		display: inline-block;
		font-size: 1rem;
		}

		.star-rating__wrap:after {
		content: "";
		display: table;
		clear: both;
		}

		.star-rating__ico {
		float: right;
		padding-left: 2px;
		cursor: pointer;
		color: #F7941D;
		font-size: 16px;
		margin-top: 5px;
		}

		.star-rating__ico:last-child {
		padding-left: 0;
		}

		.star-rating__input {
		display: none;
		}

		.star-rating__ico:hover:before,
		.star-rating__ico:hover ~ .star-rating__ico:before,
		.star-rating__input:checked ~ .star-rating__ico:before {
		content: "\F005";
		}


		.slick-slide{
			margin:0 5px
		}
		.slick-prev:before, .slick-next:before{
			color: #5db845;
		}
		.slick-prev, .slick-next{
			z-index:99;
		}
	
		.set_active{
			color: #5db845 !important;
		}
		a.btn.min.active {
			color: #fff;
			background: #5db845!important;
		}
	</style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
     @if ($errors->any() || session('return_exchange_modal'))
        <script>
            $(document).ready(function () {
                $('#returnExchangeModal').modal('show');
            });
        </script>
    @endif
    <script>

        function setButtonLoading(button, loadingText = 'Processing...') {
            if (!button) {
                return;
            }

            $(button).prop('disabled', true).addClass('btn-loading');
            $(button).data('original-text', $(button).html());
            $(button).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + loadingText);
        }

        function resetButtonLoading(button) {
            if (!button) {
                return;
            }

            $(button).prop('disabled', false).removeClass('btn-loading');
            const originalText = $(button).data('original-text');
            if (originalText) {
                $(button).html(originalText);
            }
        }

        function Updateorder(id, status, button = null){
            if(status === 'Cancell'){
                cancelorder(id, status, button);
            } else if(status == 'Return') {
                setButtonLoading(button);
                openReturnExchangeModal(id);
                resetButtonLoading(button);
            }else if(status == 'Return Cancel') {
                returnRequestCancel(id , status, button); 
            }else if(status == 'Exchange Cancel') {
                exchangeRequestCancel(id , status, button);
            } else {
                var result = false;
            }
        }

        // Open Modal
        function openReturnExchangeModal(orderId)
        {
            $('#returnExchangeModal').modal('show'); // commented now for testing direct return order 

        }

        // Exchange Size Toggle
        $('#request_type').on('change', function () {

            if ($(this).val() === 'exchange') {
                $('#exchange_size_section').removeClass('d-none');
            } else {
                $('#exchange_size_section').addClass('d-none');
            }

        });

        // Image Preview
        // Image Preview
        $('#returnImages').on('change', function () {
            $('#imagePreviewContainer').html('');
            Array.from(this.files).forEach(file => {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreviewContainer').append(`
                        <div style="width:90px; height:90px; border-radius:10px; overflow:hidden; border:1px solid #e0e0e0;">
                            <img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            });
        });


        function cancelorder(id , status = 'Cancell', button = null){
            var result = confirm('Are you sure you want to cancel this order?');
            if(result) {
                setButtonLoading(button);
                $.ajax({
                    url: "{{ route('order.update.status') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
                    },
                    success: function(response) {
                        if(response.status) {
                            alert(response.message);
                            location.reload();
                        } else {
                            resetButtonLoading(button);
                            alert('Failed to update order. Please try again.');
                        }
                    },
                    error: function() {
                        resetButtonLoading(button);
                        alert('An error occurred while updating the order. Please try again.');
                    }
                });
            }
        }

        function returnorder(id , status = 'Return', button = null){
            var result = confirm('Are you sure you want to return this order?');
            if(result) {
                setButtonLoading(button);
                $.ajax({
                    url: "{{ route('order.update.status') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
                    },
                    success: function(response) {
                        if(response.status) {
                            alert(response.message);
                            location.reload();
                        } else {
                            resetButtonLoading(button);
                            alert('Failed to update order. Please try again.');
                        }
                    },
                    error: function() {
                        resetButtonLoading(button);
                        alert('An error occurred while updating the order. Please try again.');
                    }
                });
            }
        }

        function returnRequestCancel(id , status = 'Return Cancel', button = null){
            var result = confirm('Are you sure you want to cancel this return request?');
            if(result) {
                setButtonLoading(button);
                $.ajax({
                    url: "{{ route('order.update.status') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
                    },
                    success: function(response) {
                        if(response.status) {
                            alert(response.message);
                            location.reload();
                        } else {
                            resetButtonLoading(button);
                            alert('Failed to update order. Please try again.');
                        }
                    },
                    error: function() {
                        resetButtonLoading(button);
                        alert('An error occurred while updating the order. Please try again.');
                    }
                });
            }
        }

        function exchangeRequestCancel(id , status = 'Exchange Cancel', button = null){
            var result = confirm('Are you sure you want to cancel this exchange request?');
            if(result) {
                setButtonLoading(button);
                $.ajax({
                    url: "{{ route('order.update.status') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
                    },
                    success: function(response) {
                        if(response.status) {
                            alert(response.message);
                            location.reload();
                        } else {
                            resetButtonLoading(button);
                            alert(response.message || 'Failed to cancel exchange request. Please try again.');
                        }
                    },
                    error: function() {
                        resetButtonLoading(button);
                        alert('An error occurred while cancelling the exchange request. Please try again.');
                    }
                });
            }
        }

        $(document).ready(function() {
            // Dynamic reasons based on request type
            const returnReasons = [
                "Size/Fit Issue",
                "Wrong Item Received",
                "Color/Style differs from website",
                "Defective/Damaged",
                "Other"
            ];
            const exchangeReasons = [
                "Defective/Damaged"
            ];

            function refreshReasonOptions(type) {
                let reasonSelect = $('#returnReason');
                let currentVal = reasonSelect.val();
                let reasons = type === 'exchange' ? exchangeReasons : returnReasons;

                $('#exchangeNote').toggle(type === 'exchange');
                reasonSelect.empty().append('<option value="" disabled selected>Select a reason...</option>');

                reasons.forEach(function(r) {
                    reasonSelect.append($('<option>', { value: r, text: r }));
                });

                if (reasons.includes(currentVal)) {
                    reasonSelect.val(currentVal);
                } else if (type === 'exchange') {
                    reasonSelect.val("Defective/Damaged");
                } else {
                    reasonSelect.val('');
                }

                if ($.fn.niceSelect) {
                    reasonSelect.niceSelect('update');
                }
            }

            $('input[name="request_type"]').on('change', function() {
                refreshReasonOptions($(this).val());
            });

            if ($('input[name="request_type"]:checked').length === 0) {
                $('input[name="request_type"][value="return"]').prop('checked', true);
            }

            refreshReasonOptions($('input[name="request_type"]:checked').val() || 'return');

            if (!$.validator) {
                return;
            }

            $.validator.addMethod("requiredFile", function(value, element) {
                return element.files && element.files.length > 0;
            }, "Please upload Product Images.");

            $.validator.addMethod("validReturnImages", function(value, element) {
                if (!element.files || element.files.length === 0) {
                    return true;
                }

                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                const maxSize = 5 * 1024 * 1024;

                return Array.from(element.files).every(function(file) {
                    return allowedTypes.includes(file.type) && file.size <= maxSize;
                });
            }, "Please upload JPG, PNG, or WEBP images up to 5 MB each.");

            $('#returnExchangeForm').validate({
                ignore: [], // Ensure hidden or absolutely positioned inputs are validated
                rules: {
                    request_type: { required: true },
                    cart_id: { required: true },
                    reason: { required: true },
                    "images[]": { requiredFile: true, validReturnImages: true },
                    customer_upi_id: {
                        required: function(element) {
                            return $('input[name="request_type"]:checked').val() === 'return' && $('#customer_upi_id').length > 0;
                        }
                    }
                },
                messages: {
                    request_type: { required: "Please select a Request Type." },
                    cart_id: { required: "Please select a Product." },
                    reason: { required: "Please provide a Reason." },
                    "images[]": {
                        requiredFile: "Please upload Product Images.",
                        validReturnImages: "Please upload JPG, PNG, or WEBP images up to 5 MB each."
                    },
                    customer_upi_id: { required: "Please provide your UPI ID for COD refund." }
                },
                errorElement: "span",
                errorClass: "text-danger d-block mt-2",
                errorPlacement: function(error, element) {
                    if (element.attr("name") == "request_type") {
                        error.appendTo(element.closest('.return-type-options').parent());
                    } else if (element.attr("name") == "cart_id") {
                        error.appendTo(element.closest('.return-product-list').parent());
                    } else if (element.attr("name") == "images[]") {
                        error.insertAfter(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                },
                invalidHandler: function(event, validator) {
                    if (validator.errorList.length) {
                        validator.errorList[0].element.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                },
                submitHandler: function(form) {
                    $('#submitReturnExchangeButton').prop('disabled', true).css('opacity', '0.7');
                    form.submit();
                }
            });
        });
    </script>
@endpush
