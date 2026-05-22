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
                    <small>Order Date: {{$order->created_at ? date('d-m-Y', strtotime($order->created_at)) : '-'}}</small>
                </div>
                <div class="d-flex flex-column">
                    <h6>Order Status</h6>
                    <span class="badge badge-info">{{strtoupper($order->status) ?? '-'}}</span>
                </div>
                <div class="d-flex flex-column">
                    <h6>Payment Status</h6>
                    <span class="badge badge-info">{{strtoupper($order->payment_status) ?? '-'}}</span>
                </div>

                <div class="d-flex flex-column">
                    @if($order->status == 'process' || $order->status == 'new' || $order->status == 'out for delivery')
                        <button class="btn btn-danger" type="button" onclick="Updateorder({{$order->id}} , 'Cancell')">Cancel Order</button>
                    @elseif($order->status == 'delivered')
                        <button class="btn btn-danger" type="button" onclick="Updateorder({{$order->id}} , 'Return')">Request Return / Exchange</button>
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
                                if($item->order && strtolower($item->order->status) === 'delivered'){
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
                                @if($item->order && strtolower($item->order->status) === 'delivered')
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
                                                        required></textarea>
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
                                                                required>{{ $review->review }}</textarea>
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
    <div class="modal fade" id="returnExchangeModal" tabindex="-1" role="dialog" aria-labelledby="returnExchangeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content custom-return-modal">

                <!-- Header -->
                <div class="modal-header border-0">
                    <div>
                        <h4 class="modal-title mb-1" id="returnExchangeModalLabel">
                            Request Return / Exchange
                        </h4>
                        <p class="mb-0 text-muted">
                            Submit your request with reason and product images
                        </p>
                    </div>

                    <button type="button" class="close close_btn" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">

                    <form id="returnExchangeForm" enctype="multipart/form-data">

                        @csrf

                        <input type="hidden" name="order_id" id="return_order_id">
                        <input type="hidden" name="product_id" id="return_product_id">

                        <div class="row">

                            <!-- Request Type -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Request Type <span class="text-danger">*</span>
                                </label>

                                <select class="form-control custom-input" name="request_type" id="request_type" required>
                                    <option value="">Select Request Type</option>
                                    <option value="return">Return & Refund</option>
                                    <option value="exchange">Exchange Product</option>
                                </select>
                            </div>

                            <!-- Exchange Size -->
                            <div class="col-md-6 mb-3 d-none" id="exchange_size_section">
                                <label class="form-label">
                                    Select New Size
                                </label>

                                <select class="form-control custom-input" name="exchange_size">
                                    <option value="">Choose Size</option>
                                    <option value="S">Small</option>
                                    <option value="M">Medium</option>
                                    <option value="L">Large</option>
                                    <option value="XL">XL</option>
                                </select>
                            </div>

                            <!-- Reason -->
                            <div class="col-12 mb-3">
                                <label class="form-label">
                                    Reason <span class="text-danger">*</span>
                                </label>

                                <textarea class="form-control custom-input"
                                        name="reason"
                                        rows="4"
                                        placeholder="Please explain your issue..."
                                        required></textarea>
                            </div>

                            <!-- Upload Images -->
                            <div class="col-12 mb-3">
                                <label class="form-label">
                                    Upload Product Images
                                </label>

                                <div class="custom-upload-box">
                                    <input type="file"
                                        name="images[]"
                                        id="returnImages"
                                        multiple
                                        accept="image/*">

                                    <div class="upload-content">
                                        <i class="fa fa-cloud-upload"></i>
                                        <p>Drag & Drop or Click to Upload</p>
                                        <small>You can upload multiple images</small>
                                    </div>
                                </div>

                                <!-- Preview -->
                                <div class="image-preview-container mt-3"></div>
                            </div>

                            <!-- Additional Notes -->
                            <div class="col-12 mb-3">
                                <label class="form-label">
                                    Additional Notes
                                </label>

                                <textarea class="form-control custom-input"
                                        name="notes"
                                        rows="2"
                                        placeholder="Optional notes..."></textarea>
                            </div>

                        </div>

                        <!-- Footer -->
                        <div class="modal-footer border-0 px-0 pb-0">

                            <button type="button"
                                    class="btn btn-light cancel-btn"
                                    data-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="btn submit-request-btn">
                                Submit Request
                            </button>

                        </div>

                    </form>

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


</style>    

@endsection

@push('styles')
	<style>

        /* =========================
        Return Exchange Modal
        ========================= */

        .custom-return-modal{
            border-radius: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }

        .custom-return-modal .modal-header{
            padding: 25px 30px 10px;
        }

        .custom-return-modal .modal-body{
            padding: 20px 30px 30px;
        }

        .custom-return-modal h4{
            font-weight: 700;
            color: #222;
        }

        .close_btn{
            background: #f5f5f5;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            font-size: 24px;
            line-height: 1;
            transition: 0.3s;
        }

        .close_btn:hover{
            background: #e9e9e9;
        }

        .form-label{
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .custom-input{
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            box-shadow: none !important;
            transition: 0.3s;
        }

        .custom-input:focus{
            border-color: #5db844;
        }

        .custom-upload-box{
            border: 2px dashed #5db844;
            border-radius: 15px;
            position: relative;
            padding: 35px 20px;
            text-align: center;
            background: #f8fff6;
            cursor: pointer;
            transition: 0.3s;
        }

        .custom-upload-box:hover{
            background: #f1ffed;
        }

        .custom-upload-box input[type="file"]{
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .upload-content i{
            font-size: 42px;
            color: #5db844;
            margin-bottom: 12px;
        }

        .upload-content p{
            margin-bottom: 5px;
            font-weight: 600;
            color: #222;
        }

        .upload-content small{
            color: #777;
        }

        .image-preview-container{
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .preview-image-box{
            width: 90px;
            height: 90px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .preview-image-box img{
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .submit-request-btn{
            background: #5db844;
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.3s;
        }

        .submit-request-btn:hover{
            background: #4ca136;
            color: #fff;
        }

        .cancel-btn{
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 500;
        }
        /* close for return exchange modal */

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
    <script>
        function Updateorder(id, status){
            return cancelorder(id);
            // if(status === 'Cancell'){
            //     return cancelorder(id);
            // } else if(status == 'Return') {
            //     openReturnExchangeModal(id);
            // } else {
            //     var result = false;
            // }
        }

        // Open Modal
        function openReturnExchangeModal(orderId)
        {
            $('#returnExchangeModal').modal('show');
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
        $('#returnImages').on('change', function () {

            $('.image-preview-container').html('');

            let files = this.files;

            if(files.length > 0){

                Array.from(files).forEach(file => {

                    let reader = new FileReader();

                    reader.onload = function(e){

                        $('.image-preview-container').append(`
                            <div class="preview-image-box">
                                <img src="${e.target.result}">
                            </div>
                        `);

                    }

                    reader.readAsDataURL(file);

                });

            }

        });


        function cancelorder(id){
            var result = confirm('Are you sure you want to cancel this order?');
            if(result) {
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
                            alert('Failed to update order. Please try again.');
                        }
                    },
                    error: function() {
                        alert('An error occurred while updating the order. Please try again.');
                    }
                });
            }
        }
    </script>
@endpush