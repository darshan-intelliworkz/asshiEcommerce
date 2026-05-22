@extends('frontend.layouts.master')
@section('title','Aashi-Ecommerce || Cart Page')
@section('main-content')
	<!-- Breadcrumbs -->
	<div class="breadcrumbs">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="bread-inner">
						<ul class="bread-list">
							<li><a href="{{('home')}}">Home<i class="ti-arrow-right"></i></a></li>
							<li class="active"><a href="">Cart</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Breadcrumbs -->

	<!-- Shopping Cart -->
	<div class="shopping-cart section">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<!-- Shopping Summery -->
					<table class="table shopping-summery">
						<thead>
							<tr class="main-hading">
								<th>PRODUCT</th>
								<th>NAME</th>
								<th>SIZE</th>
								<th class="text-center">UNIT PRICE</th>
								<th class="text-center">QUANTITY</th>
								<th class="text-center">TOTAL</th>
								<th class="text-center"><i class="ti-trash remove-icon"></i></th>
							</tr>
						</thead>
						<tbody id="cart_item_list">
							<form action="{{route('cart.update')}}" method="POST" id="cart-update-form">
								@csrf
								@if(Helper::getAllProductFromCart()->count() > 0)
									@foreach(Helper::getAllProductFromCart() as $key=>$cart)
										@php
											$price = json_decode($cart->size_price,true) ?? [];
										@endphp
										
										<tr>
											@php 
											$photo=explode(',',$cart->product['photo']);
											@endphp
											<td class="image" data-title="No">
												@if(isset($cart->color_img) && $cart->color_img != null) 
													<img src="{{$cart->color_img}}" alt="{{ $cart->color_img }}">
												@else
													<img src="{{asset('public/'.$photo[0])}}" alt="{{asset('public/'.$photo[0])}}">
												@endif
											</td>
											<td class="product-des" data-title="Description">
												<p class="product-name"><a href="{{route('product-detail',$cart->product['slug'])}}" target="_blank">{{$cart->product['product_code']}}@if(isset($cart->color_id) && $cart->color_id != NULL) ({{optional($cart->color)->color_name}}) @endif</a></p>
												<p class="product-des">{!!($cart['summary']) !!}</p>
											</td>
											<td class="price" data-title="Price"><span>{{ $price['size']}}</span></td>

											<td class="price" data-title="Price"><span>
											₹{{number_format($cart['price'],2)}}</span>
											</td>
											<td class="qty" data-title="Qty"><!-- Input Order -->
												<div class="input-group">
													<div class="button minus"> 
														<button type="button" class="btn btn-primary btn-number" data-type="minus" data-field="quant[{{$key}}]" min="1">
															<i class="ti-minus"></i>
														</button>
													</div>

													<input type="text" name="quant[{{$key}}]" class="input-number"  data-min="1" data-max="100" value="{{$cart->quantity}}" readonly>
													<input type="hidden" name="qty_id[]" value="{{$cart->id}}">

													<div class="button plus">
														<button type="button" class="btn btn-primary btn-number" data-type="plus" data-field="quant[{{$key}}]">
															<i class="ti-plus"></i>
														</button>
													</div>
												</div>
												<!--/ End Input Order -->
											</td>
											<td class="total-amount cart_single_price" data-title="Total"><span class="money">₹{{$cart['amount']}}</span></td>

											<td class="action" data-title="Remove"><a href="{{route('cart-delete',$cart->id)}}"><i class="ti-trash remove-icon"></i></a></td>
										</tr>
									@endforeach
									<track>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										{{-- <td class="float-right">
											<button class="btn float-right" type="submit">Update</button>
										</td> --}}
									</track>
								@else
										<tr>
											<td class="text-center">
												There are no any carts available. <a href="{{route('product-lists')}}" style="color:blue;">Continue shopping</a>

											</td>
										</tr>
								@endif

							</form>
						</tbody>
					</table>
					<!--/ End Shopping Summery -->
				</div>
			</div>
			@if(Helper::getAllProductFromCart()->count() > 0)
			<div class="row">
				<div class="col-12">
					<!-- Total Amount -->
					<div class="total-amount">
						<div class="row">
							<div class="col-lg-8 col-md-5 col-12">
								<div class="left">
									<div class="coupon">
										{{-- <form action="{{route('coupon-store')}}" method="POST">
											@csrf
											<input name="code" placeholder="Enter Your Coupon" value="{{ old('code') }}">
											<button class="btn">Apply</button>
										</form> --}}
										{{-- REMOVE COUPON --}}
										{{-- @if(session()->has('coupon'))
											<form action="{{ route('coupon-remove') }}" method="POST" class="mt-2">
												@csrf
												<button type="submit" class="btn btn-danger">
													Remove Coupon ({{ session('coupon.code') }})
												</button>
											</form>
										@endif --}}
									</div> 
									{{-- <div class="checkbox">`
										@php
											$shipping=DB::table('shippings')->where('status','active')->limit(1)->get();
										@endphp
										<label class="checkbox-inline" for="2"><input name="news" id="2" type="checkbox" onchange="showMe('shipping');"> Shipping</label>
									</div> --}}
								</div>
							</div>
							<div class="col-lg-4 col-md-7 col-12">
								<div class="right">
									<ul>
										<li class="order_subtotal" data-price="{{Helper::totalCartPrice()}}">Cart Subtotal<span>₹{{number_format(Helper::totalCartPrice(),2)}}</span></li>

										@if(session()->has('coupon'))
										<li class="coupon_price" data-price="{{Session::get('coupon')['value']}}">You Save<span>₹{{number_format(Session::get('coupon')['value'],2)}}</span></li>
										@endif
										@php
											$total_amount=Helper::totalCartPrice();
											if(session()->has('coupon')){
												$total_amount=$total_amount-Session::get('coupon')['value'];
											}
											$gstTotal = Helper::totalGstPrice();
										@endphp
										@if($gstTotal > 0)
											@php $total_amount += $gstTotal; @endphp
											<li class="last" id="gst_amount">GST Amount<span>₹{{number_format($gstTotal,2)}}</span></li>
										@endif
										@if(session()->has('coupon'))
											<li class="last" id="order_total_price">You Pay<span>₹{{number_format($total_amount,2)}}</span></li>
										@else
											<li class="last" id="order_total_price">You Pay<span>₹{{number_format($total_amount,2)}}</span></li>
										@endif
									</ul>
									<div class="button5">
										<a href="{{route('checkout')}}" class="btn">Checkout</a>
										<a href="{{route('product-lists')}}" class="btn">Continue shopping</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--/ End Total Amount -->
				</div>
			</div>
			@endif
		</div>
	</div>
	<!--/ End Shopping Cart -->

	<!-- Start Shop Services Area  -->
	<section class="shop-services section mb-4">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-md-6 col-12">
					<div class="single-service">
						<i class="ti-lock"></i>
						<h4>Sucure Payment</h4>
						<p>100% secure payment</p>
					</div>
				</div>
				<div class="col-lg-6 col-md-6 col-12">
					<div class="single-service">
						<i class="ti-tag"></i>
						<h4>Best Peice</h4>
						<p>Guaranteed price</p>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- End Shop Newsletter -->

	<!-- Start Shop Newsletter  -->
	{{-- @include('frontend.layouts.newsletter') --}}
	<!-- End Shop Newsletter -->

@endsection
@push('styles')
	<style>
		li.shipping{
			display: inline-flex;
			width: 100%;
			font-size: 14px;
		}
		li.shipping .input-group-icon {
			width: 100%;
			margin-left: 10px;
		}
		.input-group-icon .icon {
			position: absolute;
			left: 20px;
			top: 0;
			line-height: 40px;
			z-index: 3;
		}
		.form-select {
			height: 30px;
			width: 100%;
		}
		.form-select .nice-select {
			border: none;
			border-radius: 0px;
			height: 40px;
			background: #f6f6f6 !important;
			padding-left: 45px;
			padding-right: 40px;
			width: 100%;
		}
		.list li{
			margin-bottom:0 !important;
		}
		.list li:hover{
			background:#F7941D !important;
			color:white !important;
		}
		.form-select .nice-select::after {
			top: 14px;
		}
	</style>
@endpush
@push('scripts')
	<script src="{{asset('public/frontend/js/nice-select/js/jquery.nice-select.min.js')}}"></script>
	<script src="{{ asset('public/frontend/js/select2/js/select2.min.js') }}"></script>
	<script>
		$(document).ready(function() { $("select.select2").select2(); });
  		$('select.nice-select').niceSelect();
	</script>
	<script>
		$(document).ready(function(){
			$('.shipping select[name=shipping]').change(function(){
				let cost = parseFloat( $(this).find('option:selected').data('price') ) || 0;
				let subtotal = parseFloat( $('.order_subtotal').data('price') );
				let coupon = parseFloat( $('.coupon_price').data('price') ) || 0;
				// alert(coupon);
				$('#order_total_price span').text('$'+(subtotal + cost-coupon).toFixed(2));
			});

		});

		$(document).on('change', '.input-number', function () {
			clearTimeout(window.cartTimer);

			window.cartTimer = setTimeout(function () {
				$('#cart-update-form').submit();
			}, 300); // debounce
		});

	</script>

@endpush
