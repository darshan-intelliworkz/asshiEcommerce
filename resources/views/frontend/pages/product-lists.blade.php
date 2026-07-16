
@extends('frontend.layouts.master')
@section('title','Aashi-Ecommerce || PRODUCT PAGE')
<style>
	.child-category li  {
		list-style-type: disc; /* Adds default bullet points */
		margin-left: 18px; /* Adds spacing on the left */
	}
</style>
@section('main-content')

		<!-- Breadcrumbs -->
		<div class="breadcrumbs">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<div class="bread-inner">
							<ul class="bread-list">
								<li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
								<li class="active"><a href="javascript:void(0);">Shop List</a></li>
							</ul>   
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- End Breadcrumbs -->
		
			<!-- Product Style 1 -->
			<section class="product-area shop-sidebar shop-list shop section">
				<div class="container">
					<div class="row">
						<div class="col-lg-3 col-md-4 col-12">
							<div class="shop-sidebar">
                                <!-- Single Widget -->
                                <div class="single-widget category">
                                    <h3 class="title">Categories</h3>
                                    <ul class="categor-list">
										@php
											// $category = new Category();
											$menu=App\Models\Category::getAllParentWithChild();
										@endphp
										@if($menu)
										<li>
											@foreach($menu as $cat_info)
													@if($cat_info->child_cat->count()>0)
														<li><a href="{{route('product-cat',$cat_info->slug)}}"><b>{{$cat_info->title}}</b> </a>
															<ul class="child-category">
																@foreach($cat_info->child_cat as $sub_menu)
																	<li><a href="{{route('product-sub-cat',[$cat_info->slug,$sub_menu->slug])}}">{{$sub_menu->title}}</a></li>
																@endforeach
															</ul> 
														</li>
													@else
														<li ><a href="{{route('product-cat',$cat_info->slug)}}"><b>{{$cat_info->title}}</b></a></li>
													@endif
											@endforeach
										</li>
										@endif
                                        
                                    </ul>
                                </div>

								<form action="{{ url()->current() }}" method="GET">
									@if(request()->has('brand') && request()->brand)
										<input type="hidden" name="brand" value="{{ request()->brand }}" />
									@endif
									@if(request()->has('search') && request()->search)
										<input type="hidden" name="search" value="{{ request()->search }}" />
									@endif
									@if(request()->has('show') && request()->show)
										<input type="hidden" name="show" value="{{ request()->show }}" />
									@endif
									@if(request()->has('sortBy') && request()->sortBy)
										<input type="hidden" name="sortBy" value="{{ request()->sortBy }}" />
									@endif
									@if(request()->has('category') && request()->category)
										<input type="hidden" name="category" value="{{ request()->category }}" />
									@endif

									
									<div class="single-widget range">
										<h3 class="title">Shop by Price</h3>
										<div class="price-filter">
											<div class="price-filter-inner">

												@php

													$max_price = \App\Models\Product::where('status', 'Active')
														->get()
														->flatMap(function ($p) {
															// decode JSON safely
															$data = json_decode($p->size, true);
															if (!is_array($data) || empty($data['price'])) return [];
															return $data['price'];
														})
														->map(function ($price) {
															// remove non-digits (₹, commas, spaces) and cast to int
															$clean = preg_replace('/[^\d\.]/', '', (string) $price);
															return (int) $clean;
														})
														->max();

													// 2️⃣ Build price ranges dynamically
													$max = $max_price ?? 0;
													// dd($max);
													$step = ceil($max / 5); // divide into 10 equal parts max
													$ranges = [];
													$start = 100;
													while ($start < $max) {
														$end = $start + $step;
														$ranges[] = [$start, $end];
														$start = $end;
													}
												@endphp

												<ul id="price-range-list" class="price-range-list">
													@foreach ($ranges as $range)
														<li>
															<label>
																<input type="checkbox" 
																	class="price-checkbox" 
																	value="{{ $range[0] }}-{{ $range[1] }}"
																	@if(!empty($_GET['price']) && in_array($range[0].'-'.$range[1], explode(',', $_GET['price']))) checked @endif>
																{{ $range[0] }} - {{ $range[1] }}
															</label>
														</li>
													@endforeach
												</ul>

												<!--<div class="product_filter">-->
												<!--	<button type="submit" class="filter_button">Filter</button>-->
												<!--	<input type="hidden" name="price" id="price" -->
												<!--		value="@if(!empty($_GET['price'])){{ $_GET['price'] }}@endif"/>-->
												<!--</div>-->
											</div>
										</div>
									</div>
								</form>

								<!--/ End Shop By Price -->
                                <!-- Single Widget -->

                                <div class="single-widget category">
                                    <h3 class="title">Brands</h3>
                                    <ul class="categor-list">
                                        @php
                                            $brands=DB::table('brands')->orderBy('title','ASC')->where('status','active')->get();
                                        @endphp
                                        @foreach($brands as $brand)
                                            <li><a href="{{route('product-brand',$brand->slug)}}">{{$brand->title}}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                        	</div>
						</div>
						<div class="col-lg-9 col-md-8 col-12">
							<div class="row">
								<div class="col-12">
									<!-- Shop Top -->
									<div class="shop-top">
										<form action="{{ url()->current() }}" method="GET">
											@if(request()->has('brand') && request()->brand)
												<input type="hidden" name="brand" value="{{ request()->brand }}" />
											@endif
											@if(request()->has('price') && request()->price)
												<input type="hidden" name="price" value="{{ request()->price }}" />
											@endif
											@if(request()->has('search') && request()->search)
												<input type="hidden" name="search" value="{{ request()->search }}" />
											@endif
											@if(request()->has('category') && request()->category)
												<input type="hidden" name="category" value="{{ request()->category }}" />
											@endif
											<div class="shop-shorter">
												<div class="single-shorter">
													<label>Show :</label>
													<select class="show" name="show" onchange="this.form.submit();">
														<option value="">Default</option>
														<option value="9" @if(!empty($_GET['show']) && $_GET['show']=='9') selected @endif>09</option>
														<option value="15" @if(!empty($_GET['show']) && $_GET['show']=='15') selected @endif>15</option>
														<option value="21" @if(!empty($_GET['show']) && $_GET['show']=='21') selected @endif>21</option>
														<option value="30" @if(!empty($_GET['show']) && $_GET['show']=='30') selected @endif>30</option>
													</select>
												</div>
												<div class="single-shorter">
													<label>Sort By :</label>
													<select class='sortBy' name='sortBy' onchange="this.form.submit();">
														<option value="">Default</option>
														<option value="title" @if(!empty($_GET['sortBy']) && $_GET['sortBy']=='title') selected @endif>Name</option>
														<option value="price" @if(!empty($_GET['sortBy']) && $_GET['sortBy']=='price') selected @endif>Price</option>
														<!--<option value="category" @if(!empty($_GET['sortBy']) && $_GET['sortBy']=='category') selected @endif>Category</option>-->
														<!--<option value="brand" @if(!empty($_GET['sortBy']) && $_GET['sortBy']=='brand') selected @endif>Brand</option>-->
													</select>
												</div>
											</div>
											<!--@if(isset($sub_slug))-->
											<!--	<ul class="view-mode">-->
											<!--		<li><a href="{{ route('productlist-with-sub', ['slug' => $category->slug, 'sub_slug' => $sub_slug]) }}"><i class="fa fa-th-list"></i></a></li>-->
											<!--		<li><a href="{{route('product-subgrids', ['slug' => $category->slug, 'sub_slug' => $sub_slug])}}"><i class="fa fa-th-large"></i></a></li>-->
											<!--	</ul>-->
											<!--@elseif(isset($category))-->
											<!--	<ul class="view-mode">-->
											<!--		<li><a href="{{ route('productlist', $category->slug) }}"><i class="fa fa-th-list"></i></a></li>-->
											<!--		<li><a href="{{route('product-grids', $category->slug)}}"><i class="fa fa-th-large"></i></a></li>-->
										
											<!--	</ul>-->
											<!--@endif-->
										</form>
									</div>
									<!--/ End Shop Top -->
								</div>
							</div>
							<div class="row">
							   
								@if($products)
									@foreach($products as $product)
										<!-- Start Single List -->
										<div class="col-12">
											<div class="row">
												<div class="col-lg-4 col-md-6 col-sm-6">
													<div class="single-product">
														<div class="product-img">
															<a href="{{route('product-detail',$product->slug)}}">
															@php 
																$photo=explode(',',$product->photo);
															@endphp
															<img class="default-img" src="{{asset('public/'.$photo[0])}}" alt="{{asset('public/'.$photo[0])}}">
															@if(isset($photo[1]))
															    <img class="hover-img" src="{{asset('public/'.$photo[1])}}" alt="{{asset('public/'.$photo[0])}}">
															@endif
															</a>
															<div class="button-head">
																<div class="product-action">
																	<!--<a data-toggle="modal" data-target="#{{$product->id}}" title="Quick View" href="#"><i class=" ti-eye"></i><span>Quick Shop</span></a>-->
																	<a title="Wishlist" href="{{route('add-to-wishlist',$product->slug)}}" class="wishlist" data-id="{{$product->id}}"><i class=" ti-heart "></i><span>Add to Wishlist</span></a>
																</div>
																<div class="product-action-2">
																	<a title="Add to cart" href="{{route('add-to-cart',$product->slug)}}">Add to cart</a>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-lg-8 col-md-6 col-12">
													<div class="list-content">
														<div class="product-content">
															<h3 class="title"><a href="{{route('product-detail',$product->slug)}}">{{$product->product_code}} </a></h3>
															
	
															@php

																$productPrice = 0;
																$sizeData = [];

																// Try decode JSON
																$decoded = json_decode($product->size, true);

																// Check if valid JSON AND has price
																if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['price'])) {

																	$sizeData = $decoded; // ✅ assign ONLY when valid

																	$priceArr = $decoded['price'] ?? [];
																	$productPrice = $priceArr[0] ?? 0;

																} else {

																	// OLD DATA fallback
																	$productPrice = $product->price ?? 0;

																	// ✅ Make sizeData safe fallback structure
																	$sizeData = [
																		'size' => [],
																		'price' => [$productPrice]
																	];
																}

																// Discount calculation
																$after_discount = $productPrice - (($productPrice * $product->discount) / 100);

																@endphp

											
															<div class="product-price pt-2" data-discount="{{$product->discount ?? 0 }}">
																<small class="original-price @if(empty($product->discount)) d-none @endif">
																	<del class="text-muted">
																		₹{{ number_format($sizeData['price'][0], 2) }}
																	</del>
																</small>
																<span class="final-price">
																	@if(!empty($product->discount))
																		₹{{ number_format($after_discount, 2) }}
																	@else
																		₹{{ number_format($sizeData['price'][0], 2) }}
																	@endif
																</span>
															</div>


															<div class="product-sizes mt-3 d-flex gap-2">
																@foreach($sizeData['size'] as $key => $size)
																	<label class="size-box">
																		<input 
																			type="radio" 
																			name="product_size_{{ $product->id }}"  
																			value="{{ $size }}" 
																			data-price="{{ $sizeData['price'][$key] }}"
																		    data-discount="{{ $product->discount ?? 0 }}"
																		    data-discount-val="{{ $after_discount ?? 0 }}"
																		    data-price-id="{{ $sizeData['price'][$key] }}" 
                        													data-size-id="{{ $sizeData['size'][$key] }}" 
                        													class="one {{ $key == 0 ? 'set_active' : '' }}" 
                        													onclick="setPriceId(this)"	
																		>
																		{{-- <span class="@if($key === 0) selected @endif {{ $key }}">{{ $size }}</span> --}}
																		<span class="{{ $key }}">{{ $size }}</span>
																	</label>
																@endforeach
															</div>


														{{-- <p>{!! html_entity_decode($product->title) !!}</p> --}}
														</div> 

														{{-- check this prodcut is alredy wishlisted or not --}}

														<?php
															$defaultColorId = $product->color->first()->id ?? null;
															$whishlist_check = App\Models\Wishlist::where('user_id', Auth::id() ?? 0)->where('product_id', $product->id);
															if($defaultColorId != null){
																$whishlist_check = $whishlist_check->where('color_id', $defaultColorId);
															}
															$whishlist_check = $whishlist_check->first();
															$wishlisted = "";
															if ($whishlist_check) {
																$wishlisted = "active";
															} else {
																$wishlisted = "";
															}
															
															$sizeData = json_decode($product->size, true);
														?>

														<p class="des pt-2">{!! html_entity_decode($product->title) !!}</p>
															<div class="add-to-cart mt-4">
                    											<form action="{{ route('add-to-carts', $product->slug) }}" method="POST" id="cartForm">
																	@csrf

																    @if($product->stock > 0)
																		<a href="javascript:void(0);" data-product-slug="{{ $product->slug }}" class="add-to-wishlist btn min {{ $wishlisted }}"><i class="ti-heart"></i></a>
																		<input type="hidden" name="selected_size" class="selected_size" value="{{ $sizeData['size'][0] ?? '' }}">
																		<input type="hidden" name="selected_price" class="selected_price" value="{{ $sizeData['price'][0] ?? '' }}">
																		<input type="hidden" name="selected_color" id="selected_color" value="{{ $product->color->first()?->id }}">

																		<button type="submit" class="btn mx-1">Add to cart</button>
																		<a href="{{ route('add-to-cart', ['slug' => $product->slug, 'buy_now' => "buyNow", 'color_id' => $product->color->first()?->id]) }}" 
																			class="btn cart" 
																			data-id="{{ $product->id }}">
																			Buy Now!
																		</a> 
																	@else
																		<span class="text-danger">Out of Stock</span>
																	@endif
																</form>

                    										</div>
													</div>
												</div>
											</div>
										</div> 
										<!-- End Single List -->
									@endforeach
								@else
									<h4 class="text-warning" style="margin:100px auto;">There are no products.</h4>
								@endif
							</div>
							<div class="row">
								<div class="col-md-12 justify-content-center d-flex">
									@if ($products->lastPage() > 1)
										<nav class="custom-pagination-wrap" aria-label="Page navigation">
											<ul class="custom-pagination">
												<li class="prev @if(!$products->previousPageUrl()) disabled @endif">
													<a href="{{ $products->previousPageUrl() ?: 'javascript:void(0);' }}">&laquo;</a>
												</li>
												@for ($i = 1; $i <= $products->lastPage(); $i++)
													<li class="page-item @if($products->currentPage() == $i) active @endif">
														<a class="page-link" href="{{ $products->url($i) }}">{{ $i }}</a>
													</li>
												@endfor
												<li class="next @if(!$products->nextPageUrl()) disabled @endif">
													<a href="{{ $products->nextPageUrl() ?: 'javascript:void(0);' }}">&raquo;</a>
												</li>
											</ul>
										</nav>
									@endif
								</div>
		
                          	</div>
						</div>
					</div>
				</div>
			</section>
			<!--/ End Product Style 1  -->	
		{{-- </form> --}}
		<!-- Modal -->
		@if($products)
			@foreach($products as $key=>$product)
				<div class="modal fade" id="{{$product->id}}" tabindex="-1" role="dialog">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span class="ti-close" aria-hidden="true"></span></button>
								</div>
								<div class="modal-body">
									<div class="row no-gutters">
										<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
											<!-- Product Slider -->
												<div class="product-gallery">
													<div class="quickview-slider-active">
														@php 
															$photo=explode(',',$product->photo);
														// dd($photo);
														@endphp
														@foreach($photo as $data)
															<div class="single-slider">
																<img src="{{asset('public/'.$data)}}" alt="{{asset('public/'.$data)}}">
															</div>
														@endforeach
													</div>
												</div>
											<!-- End Product slider -->
										</div>
										<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
											<div class="quickview-content">
												<h2>{{$product->title}}</h2>
												<div class="quickview-ratting-review">
													<div class="quickview-ratting-wrap">
														<div class="quickview-ratting">
															
															@php
																$rate=DB::table('product_reviews')->where('product_id',$product->id)->avg('rate');
																$rate_count=DB::table('product_reviews')->where('product_id',$product->id)->count();
															@endphp
															@for($i=1; $i<=5; $i++)
																@if($rate>=$i)
																	<i class="yellow fa fa-star"></i>
																@else 
																<i class="fa fa-star"></i>
																@endif
															@endfor
														</div>
														<a href="#"> ({{$rate_count}} customer review)</a>
													</div>
													<div class="quickview-stock">
														@if($product->stock >0)
														<span><i class="fa fa-check-circle-o"></i> {{$product->stock}} in stock</span>
														@else 
														<span><i class="fa fa-times-circle-o text-danger"></i> {{$product->stock}} out stock</span>
														@endif
													</div>
												</div>
												
													//$after_discount=($product->price-($product->price*$product->discount)/100);
													@php

														$productPrice = 0;

															// Try decode JSON
															$sizes = json_decode($product->size);

															// Check valid JSON + price exists
															if (json_last_error() === JSON_ERROR_NONE && is_object($sizes) && isset($sizes->price)) {

																$priceArr = $sizes->price ?? [];
																$productPrice = $priceArr[0] ?? 0;

															} else {

																// OLD DATA fallback
																$productPrice = $product->price ?? 0;

																// ✅ make $sizes safe so it won't break later
																$sizes = (object)[
																	'size' => [],
																	'price' => [$productPrice]
																];
															}

															// Discount
															$after_discount = ($productPrice - ($productPrice * $product->discount) / 100);

															@endphp
												
												<h3><small>
													<del class="text-muted">₹{{number_format($productPrice,2)}}</del>
												   </small>   
												 	₹{{number_format($after_discount,2)}}  
												</h3>
												<div class="quickview-peragraph">
													<p>{!! html_entity_decode($product->summary) !!}</p>
												</div>
												@if($product->size)
													<div class="size">
														<h4>Size</h4>
														<ul>
															@php 
																//$sizes=explode(',',$product->size);
															@endphp
															@foreach($sizes->size as $size)
															<li><a href="#" class="one">{{$size}}</a></li>
															@endforeach
														</ul>
													</div>
												@endif
												<form action="{{route('single-add-to-cart')}}" method="POST">
													@csrf 
													<div class="quantity">
														<!-- Input Order -->
														<div class="input-group">
															<div class="button minus">
																<button type="button" class="btn btn-primary btn-number" disabled="disabled" data-type="minus" data-field="quant[1]">
																	<i class="ti-minus"></i>
																</button>
															</div>
															<input type="hidden" name="slug" value="{{$product->slug}}">
															<input type="text" name="quant[1]" class="input-number"  data-min="1" data-max="1000" value="1">
															<div class="button plus">
																<button type="button" class="btn btn-primary btn-number" data-type="plus" data-field="quant[1]">
																	<i class="ti-plus"></i>
																</button>
															</div>
														</div>
														<!--/ End Input Order -->
													</div>
													<div class="add-to-cart">
														<button type="submit" class="btn">Add to cart</button>
														<a href="{{route('add-to-wishlist',$product->slug)}}" class="btn min"><i class="ti-heart"></i></a>
													</div>
												</form>
												<div class="default-social">
												<!-- ShareThis BEGIN --><div class="sharethis-inline-share-buttons"></div><!-- ShareThis END -->
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
				</div>
			@endforeach
		@endif
			<!-- Modal end -->
@endsection
@push ('styles')
<style>
	 .pagination{
        display:inline-flex;
    }
	.filter_button{
        /* height:20px; */
        text-align: center;
        background:#F7941D;
        padding:8px 16px;
        margin-top:10px;
        color: white;
    }

	.product-sizes
	{ 
		display: flex;
		align-items: center;
		gap: 2px;
		margin-top: 15px;
	}

	.size-box {
		cursor: pointer;
		display: flex;
		align-items: center;
		transition: 0.2s;
		font-weight: 500;
	}

	



	/* Hover effect (optional) */
	.size-box:focus-visible {
		outline: none;
	}

	.size-box input {
		display: none; /* Hide actual radio */
	}

	/* When selected - only change border */
	.size-box input:checked + span {
		border: 2px solid #000;
		padding: 6px 12px;
		border-radius: 6px;
		font-weight: 600;
	}

	.size-box span.selected
	{
       border: 2px solid #000;
	}

	.size-box input + span
	{
		border: 2px solid white;
		padding: 6px 12px;
		border-radius: 6px;
			font-weight: 600;
	}

	.size-box input:hover + span {
		border: 2px solid #000;
			
	}
	a.btn.min.active {
    background: #5db845;
    color: #fff;
    border-color: transparent;
}

	/* Custom compact pagination */
	.custom-pagination-wrap { margin: 16px 0; }
	.custom-pagination { display: inline-flex; gap: 6px; list-style: none; padding: 0; margin: 0; }
	.custom-pagination .page-item a, .custom-pagination .prev a, .custom-pagination .next a { display:inline-block; padding:6px 10px; border:1px solid #e6e6e6; color:#666; min-width:34px; text-align:center; background:#fff; border-radius:3px; font-size:13px; }
	.custom-pagination .page-item.active a { background:#F7941D; color:#fff; border-color:#F7941D; }
	.custom-pagination .prev a, .custom-pagination .next a { font-weight:700; }
	.custom-pagination .disabled a { opacity:0.45; pointer-events:none; }
	.custom-pagination-wrap { display:flex; justify-content:center; width:100%; }

</style>
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

    
<script>
	

	$(document).ready(function() {
		$('.price-checkbox').on('change', function() {
			// Allow only one selection at a time
			$('.price-checkbox').not(this).prop('checked', false);

			let selected = $(this).is(':checked') ? $(this).val() : null;

			// Update hidden GET field and submit the form on the same URL
			$('#price').val(selected ? selected : '');
			$(this).closest('form').submit();
		});
		
		$('.add-to-wishlist').on('click', function (e) {
			e.preventDefault();
			var productSlug = $(this).attr('data-product-slug');
			var colorId = $('#selected_color').val();
			var baseUrl = "{{ url('wishlist') }}/" + productSlug;

			window.location.href = baseUrl + '?color_id=' + colorId;
		});
	});


</script>

{{-- size wise price change script --}}
<script>


// NEW 2
document.querySelectorAll('input[type="radio"][name^="product_size"]').forEach(radio => {

    radio.addEventListener('change', function () {

        let productBox = this.closest('.list-content');

        let priceWrapper = productBox.querySelector('.product-price');
        let originalEl   = productBox.querySelector('.original-price del');
        let originalWrap = productBox.querySelector('.original-price');
        let finalEl      = productBox.querySelector('.final-price');

        let sizeInput  = productBox.querySelector('.selected_size');
        let priceInput = productBox.querySelector('.selected_price');

        let basePrice = parseFloat(this.dataset.price);
        let discount  = parseFloat(this.dataset.discount || 0);

        let finalPrice = basePrice;

        if (discount > 0) {
            finalPrice = basePrice - (basePrice * discount / 100);
            originalWrap.classList.remove('d-none');
            originalEl.innerText = "₹" + basePrice.toFixed(2);
        } else {
            originalWrap.classList.add('d-none');
        }

        finalEl.innerText = "₹" + finalPrice.toFixed(2);

        sizeInput.value  = this.value;
        priceInput.value = finalPrice.toFixed(2);

        productBox.querySelectorAll('.size-box span')
            .forEach(span => span.classList.remove('selected'));

        this.nextElementSibling.classList.add('selected');
    });
});

// auto select first size
document.querySelectorAll('.product-sizes').forEach(box => {
    let first = box.querySelector('input[type="radio"]');
    if(first){
        first.checked = true;
        first.dispatchEvent(new Event('change'));
    }
});
</script>


@endpush