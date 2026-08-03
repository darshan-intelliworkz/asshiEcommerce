@extends('frontend.layouts.master')
@section('meta')
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name='copyright' content=''>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="keywords" content="online shop, purchase, cart, ecommerce site, best online shopping">
<meta name="description" content="{{$product_detail->summary}}">
<meta property="og:url" content="{{route('product-detail',$product_detail->slug)}}">
<meta property="og:type" content="article">
<meta property="og:title" content="{{$product_detail->title}}">
<meta property="og:image" content="{{$product_detail->photo}}">
<meta property="og:description" content="{{$product_detail->description}}">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick-theme.css"/>
@endsection
@section('title','E-SHOP || PRODUCT DETAIL')
@section('main-content')
<!-- Breadcrumbs -->
<div class="breadcrumbs">
   <div class="container">
      <div class="row">
         <div class="col-12">
            <div class="bread-inner">
               <ul class="bread-list">
                  <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                  <li class="active"><a href="">Shop Details</a></li>
               </ul>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- End Breadcrumbs -->
<!-- Shop Single -->
<section class="shop single section pb-5">
   <div class="container">
      <div class="row">
         <div class="col-12">
            <div class="row">
               <div class="col-lg-6 col-12">
                  <!-- Product Slider -->
                  @if($product_detail->color->first())
                  <div class="product-gallery">
                     <div class="main-banner">
                        {{-- Show video first if exists --}}
                        @if(!empty($product_detail->video))
                        <div>
                           <video width="100%" height="400" controls>
                              <source src="{{ asset('public/product_videos/'.$product_detail->video) }}" type="video/mp4">
                              Your browser does not support the video tag.
                           </video>
                        </div>
                        @endif
                        @foreach($product_detail->color->first()->images as $image)
                        <img src="{{ asset('public/storage/products/'.$image->image) }}" alt="{{ $product_detail->color->first()->color_name }}">
                        @endforeach
                     </div>
                     <div class="small-banner">
                        {{-- Video thumbnail --}}
                        @if(!empty($product_detail->video))
                        <div>
                           <video width="100" height="80">
                              <source src="{{ asset('public/product_videos/'.$product_detail->video) }}" type="video/mp4">
                           </video>
                        </div>
                        @endif
                        @foreach($product_detail->color->first()->images as $image)
                        <img src="{{ asset('public/storage/products/'.$image->image) }}" alt="{{ $product_detail->color->first()->color_name }}">
                        @endforeach
                     </div>
                  </div>
                  @else 
                  <div class="flexslider-thumbnails">
                     <ul class="slides">
                        @php 
                        $photo=explode(',',$product_detail->photo);
                        // dd($photo);
                        @endphp
                        @foreach($photo as $data)
                        <li data-thumb="{{asset('public/'.$data)}}" rel="adjustX:10, adjustY:">
                           <img src="{{asset('public/'.$data)}}" alt="{{asset('public/'.$data)}}">
                        </li>
                        @endforeach
                     </ul>
                  </div>
                  @endif
                  <!-- End Product slider -->
               </div>
               <div class="col-lg-6 col-12" >
                  <div class="product-des">
                     <!-- Description -->
                     <div class="short">
                        <h4>{{$product_detail->title}}</h4>
                        <h5 class="description">{{$product_detail->product_code }}</h5>
                        <div class="rating-main">
                           <ul class="rating">
                              @php
                              $rate=ceil($product_detail->getReview->avg('rate'))
                              @endphp
                              @for($i=1; $i<=5; $i++)
                              @if($rate>=$i)
                              <li><i class="fa fa-star"></i></li>
                              @else 
                              <li><i class="fa fa-star-o"></i></li>
                              @endif
                              @endfor
                           </ul>
                           <a href="javascript:void(0);"class="total-review">({{$product_detail['getReview']->count()}}) Review</a>
                        </div>
                        @php 
                        //$after_discount=($product_detail->price-(($product_detail->price*$product_detail->discount)/100));
                        $sizes = json_decode($product_detail->size);
                        $priceArr = $sizes->price;
                        $productPrice = 0;
                        //foreach($priceArr as $k => $v){
                        $productPrice = $priceArr[0];
                        //}
                        $after_discount=($productPrice-($productPrice*$product_detail->discount)/100);
                        $sizeData = json_decode($product_detail->size, true);
                        @endphp
                        {{-- @if($product_detail->discount > 0)
                        @if(isset($sizeData['price'][0]) && is_numeric($sizeData['price'][0]))
                        <p class="price" > <small><del class="text-muted">₹{{number_format($sizeData['price'][0], 2)}}</del></small> <span class ="discount" id="targetDivId" >₹{{ number_format($after_discount, 2) }}</span></p>
                        @endif
                        @else
                        <p class="price" ><span  class ="discount" id="targetDivId" >₹{{ number_format($sizeData['price'][0], 2) }}</span></p>
                        @endif --}}
                        <p class="price" data-discount="{{ $product_detail->discount }}">
                           @if($product_detail->discount > 0)
                           <small class="original-price">
                           <del class="text-muted">
                           ₹{{ number_format($sizeData['price'][0], 2) }}
                           </del>
                           </small>
                           <span class="discounted-price" id="targetDivId">
                           ₹{{ number_format($after_discount, 2) }}
                           </span>
                           @else
                           <span class="discounted-price" id="targetDivId">
                           ₹{{ number_format($sizeData['price'][0], 2) }}
                           </span>
                           @endif
                        </p>
                     </div>
                     <!--/ End Description -->
                     <!-- Color -->
                     @if($product_detail->color->first())	
                     <div class="color mt-4">
                        <h4>Available Options <span>Color</span></h4>
                        <!--<ul id="color-options">-->
                        <!--	@foreach($product_detail->color as $key => $color)-->
                        <!--		<li>-->
                        <!--			<a style="background-color: {{ $color->color_code }};" href="javascript:void(0);" class="one color-selector {{ $key == 0 ? 'set_active' : '' }}" data-color-id="{{ $color->id }}" data-color-name="{{ $color->color_name }}" onclick="setPriceId(this)">-->
                        <!--				<i class="ti-check {{ $loop->first ? 'selected' : '' }}"></i>-->
                        <!--			</a>-->
                        <!--		</li>-->
                        <!--	@endforeach-->
                        <!--</ul>-->
                        <ul id="color-options">
                           @foreach($product_detail->color as $key => $color)
                           @php
                           $isWhite = strtolower($color->color_code) == '#ffffff' 
                           || strtolower($color->color_code) == '#fff'
                           || strtolower($color->color_name) == 'white';
                           @endphp
                           <li>
                              <a 
                                 style="
                                 background-color: {{ $color->color_code }};
                                 border: {{ $isWhite ? '1px solid #eee' : '1px solid transparent' }};
                                 "
                                 href="javascript:void(0);" 
                                 class="one color-selector {{ $key == 0 ? 'set_active' : '' }}" 
                                 data-color-id="{{ $color->id }}" 
                                 data-color-name="{{ $color->color_name }}" 
                                 onclick="setPriceId(this)"
                                 >
                              <i class="ti-check {{ $loop->first ? 'selected' : '' }}" 
                                 style="color: {{ $isWhite ? '#000' : '#fff' }};">
                              </i>
                              </a>
                           </li>
                           @endforeach
                        </ul>
                     </div>
                     @endif
                     <!--/ End Color -->
                     <!-- Size -->
                     @if($product_detail->size)
                     <div class="size mt-4  product-sizeoptions">
                        <h4>Size</h4>
                        <ul id="size-options">
                           @php 
                           $sizes = json_decode($product_detail->size, true);
                           @endphp
                           @foreach($sizes['size'] as $key => $size)
                           <li><a href="javascript:void(0);" 
                              data-price-id="{{ $sizes['price'][$key] }}" 
                              data-size-id="{{ $sizes['size'][$key] }}" 
                              class="one {{ $key == 0 ? 'set_active' : '' }}" 
                              onclick="setPriceId(this)">{{ $size }}</a>
                           </li>
                           @endforeach
                        </ul>
                     </div>
                     @endif
                     <!--/ End Size -->
                     <!-- Product Buy -->
                     <div class="">
                        <form action="{{route('single-add-to-cart')}}" method="POST">
                           @csrf 
                           @if($product_detail->stock > 0)
                           <div class="quantity mt-3">
                              <h6>Quantity :</h6>
                              <!-- Input Order -->
                              <div class="input-group">
                                 <div class="button minus">
                                    <button type="button" class="btn btn-primary btn-number" disabled="disabled" data-type="minus" data-field="quant[1]">
                                    <i class="ti-minus"></i>
                                    </button>
                                 </div>
                                 <input type="hidden" name="slug" value="{{$product_detail->slug}}">
                                 <input type="text" name="quant[1]" class="input-number"  data-min="1" data-max="1000" value="1" id="quantity">
                                 <div class="button plus">
                                    <button type="button" class="btn btn-primary btn-number" data-type="plus" data-field="quant[1]">
                                    <i class="ti-plus"></i>
                                    </button>
                                 </div>
                              </div>
                              <!--/ End Input Order -->
                           </div>
                           @endif
                           <?php
                              $defaultColorId = $product_detail->color->first()->id ?? null;
                              $whishlist_check = App\Models\Wishlist::where('user_id', Auth::id() ?? 0)->where('product_id', $product_detail->id);
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
                              ?>
                           <div class="add-to-cart mt-4">
                              <input type="hidden" name="selected_size" id="selected_size" value="{{ $sizes['size'][0] ?? '' }}">
                              <input type="hidden" name="selected_price" id="selected_price" value="{{ $sizeData['price'][0], 2 ?? '' }}">
                              <input type="hidden" name="selected_color" id="selected_color" value="{{ $product_detail->color[0]->id }}">
                              <input type="hidden" name="selected_color_name" id="selected_color_name" value="{{ $product_detail->color[0]->color_name }}">
                              @if($product_detail->stock > 0)
                              <button type="submit" class="btn">Add to cart</button>
                              {{-- <a href="{{route('add-to-wishlist',$product_detail->slug)}}" class="btn min {{ $wishlisted }}"><i class="ti-heart"></i></a> --}}
                              <a href="javascript:void(0);" class="add-to-wishlist btn min {{ $wishlisted }}"><i class="ti-heart"></i></a>
                              <a href="{{ route('add-to-cart', ['slug' => $product_detail->slug, 'buy_now' => "buyNow", 'color_id' => $product_detail->color[0]->id]) }}" 
                              class="btn cart buy-now-btn" 
                              data-id="{{ $product_detail->id }}">
                              Buy Now!
                              </a>
                              @else
                              <button type="button" class="btn" disabled>Out of Stock</button>
                              @endif
                           </div>
                           <div class="product-share mt-3">
                              <h6>Share Product :</h6>
                              @php
                              $shareUrl = route('product-detail', $product_detail->slug);
                              $shareText = urlencode($product_detail->title . ' - Check this product');
                              @endphp
                              <a href="https://wa.me/?text={{ $shareText }}%20{{ urlencode($shareUrl) }}" 
                                 target="_blank" 
                                 class="share-btn whatsapp">
                              <i class="fa fa-whatsapp"></i>
                              </a>
                              <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" 
                                 target="_blank" 
                                 class="share-btn facebook">
                              <i class="fa fa-facebook"></i>
                              </a>
                              <a href="https://www.instagram.com/" 
                                 target="_blank" 
                                 class="share-btn instagram">
                              <i class="fa fa-instagram"></i>
                              </a>
                           </div>
                        </form>
                        <p class="cat">Category :<a href="{{route('product-cat',$product_detail->cat_info['slug'])}}">{{$product_detail->cat_info['title']}}</a></p>
                        @if($product_detail->sub_cat_info)
                        <p class="cat mt-1">Sub Category :<a href="{{route('product-sub-cat',[$product_detail->cat_info['slug'],$product_detail->sub_cat_info['slug']])}}">{{$product_detail->sub_cat_info['title']}}</a></p>
                        @endif
                       @if($product_detail->size_chart)
                          <p class="mt-3"> <a class="View-Size-Chart" href="javascript:void(0);" data-toggle="modal" data-target="#sizeChartModal">
                                 <b>View Size Chart</b>
                           </a></p>
                        @endif
                        <!-- <p class="availability">Stock : @if($product_detail->stock>0)<span class="badge badge-success">{{$product_detail->stock}}</span>@else <span class="badge badge-danger">{{$product_detail->stock}}</span>  @endif</p> -->
                        @if($product_detail->product_features)
                        <p class="cat mb-2"> <b style="font-size:20px;">Features :</b> {!! $product_detail->product_features !!}</p>
                        @endif
                     </div>
                     {{-- 
                     <div class="row">
                        <div class="col-12">
                           <div class="product-info">
                              <div class="nav-main">
                                 <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#description" role="tab">Description</a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#reviews" role="tab">Reviews</a></li>
                                 </ul>
                              </div>
                              <div class="tab-content" id="myTabContent">
                                 <div class="tab-pane fade show active" id="description" role="tabpanel">
                                    <div class="tab-single">
                                       <div class="row">
                                          <div class="col-12">
                                             <div class="single-des">
                                                <p>{!! ($product_detail->description) !!}</p>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="tab-pane fade" id="reviews" role="tabpanel">
                                    <div class="tab-single review-panel">
                                       <div class="row">
                                          <div class="col-12">
                                             <div class="ratting-main">
                                                <div class="avg-ratting">
                                                   <h4>{{ceil($product_detail->getReview->avg('rate'))}} <span>(Overall)</span></h4>
                                                   <span>Based on {{$product_detail->getReview->count()}} Comments</span>
                                                </div>
                                                @foreach($product_detail['getReview'] as $data)
                                                <div class="single-rating">
                                                   <div class="rating-author">
                                                      @if($data->user_info['photo'])
                                                      <img src="{{asset('public/'.$data->user_info['photo'])}}" alt="{{$data->user_info['photo']}}">
                                                      @else 
                                                      <img src="{{asset('public/backend/img/avatar.png')}}" alt="Profile.jpg">
                                                      @endif
                                                   </div>
                                                   <div class="rating-des">
                                                      <h6>{{$data->user_info['name']}}</h6>
                                                      <div class="ratings">
                                                         <ul class="rating">
                                                            @for($i=1; $i<=5; $i++)
                                                            @if($data->rate>=$i)
                                                            <li><i class="fa fa-star"></i></li>
                                                            @else 
                                                            <li><i class="fa fa-star-o"></i></li>
                                                            @endif
                                                            @endfor
                                                         </ul>
                                                         <div class="rate-count">(<span>{{$data->rate}}</span>)</div>
                                                      </div>
                                                      <p>{{$data->review}}</p>
                                                   </div>
                                                </div>
                                                @endforeach
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     --}}
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
<section class="shop single pt-0 pb-5">
   <div class="container">
      <div class="row">
         <div class="col-12">
            <div class="product-info mt-0">
               <div class="nav-main">
                  <ul class="nav nav-tabs" id="myTab" role="tablist">
                     <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#description" role="tab">Description</a></li>
                     <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#reviews" role="tab">Reviews</a></li>
                  </ul>
               </div>
               <div class="tab-content" id="myTabContent">
                  <div class="tab-pane fade show active" id="description" role="tabpanel">
                     <div class="tab-single">
                        <div class="row">
                           <div class="col-12">
                              <div class="single-des">
                                 <p>{!! ($product_detail->description) !!}</p>
                                 @if($product_detail->cat_info->slug == 'rainwear')
                                 <div id="accordion" class="custom-accordion mt-4">
                                    <!-- Item 1 -->
                                    @php $productName = strtolower($product_detail->product_code); @endphp
                                    @if(!in_array($productName, ['apt poncho', 'apn poncho', 'jlpc-101', 'rlpc-104']))
                                    <div class="card">
                                       <div class="card-header">
                                          <button 
                                             class="btn btn-link" 
                                             data-toggle="collapse" 
                                             data-target="#collapseOne">
                                          <i class="fa fa-arrows-alt accordion-left-icon"></i>
                                          Size Guide
                                          <span class="plus-minus">
                                          <span class="fa fa-plus"></span>
                                          <span class="fa fa-minus"></span>
                                          </span>
                                          </button>
                                       </div>
                                       <div id="collapseOne" class="collapse show" data-parent="#accordion">
                                          <div class="card-body">
                                             <ul>
                                                <li>Choose a raincoat that is one size larger than your regular shirt size for a comfortable fit.</li>
                                                <li>If you plan to wear a backpack inside the raincoat, we recommend choosing two sizes larger. (Do not Put this on APT, APN, JLPC, RLPC code)</li>
                                             </ul>
                                          </div>
                                       </div>
                                    </div>
                                    @endif
                                    <!-- Item 2 -->
                                    <div class="card">
                                       <div class="card-header">
                                          <button 
                                             class="btn btn-link collapsed" 
                                             data-toggle="collapse" 
                                             data-target="#collapseTwo">
                                          <i class="fa fa-truck accordion-left-icon"></i>
                                          Shipping Instructions
                                          <span class="plus-minus">
                                          <i class=" fa fa-plus"></i>
                                          <i class="fa fa-minus"></i>
                                          </span>
                                          </button>
                                       </div>
                                       <div id="collapseTwo" class="collapse" data-parent="#accordion">
                                          <div class="card-body">
                                             <ul>
                                                <li>Orders are usually processed within 24–48 hours after confirmation.</li>
                                                <li>Delivery timelines may vary depending on your location and courier availability</li>
                                                <li>Please provide a complete and accurate shipping address with a valid contact number.</li>
                                                <li>Delivery timelines may be affected due to bad weather conditions or unforeseen courier delays.</li>
                                                <li>Once your order is shipped, tracking details will be shared via SMS or email.</li>
                                             </ul>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- Item 3 -->
                                    <div class="card">
                                       <div class="card-header">
                                          <button 
                                             class="btn btn-link collapsed" 
                                             data-toggle="collapse" 
                                             data-target="#collapseThree">
                                          <i class="fa fa-exchange accordion-left-icon"></i>
                                          7-Day Easy Exchange
                                          <span class="plus-minus">
                                          <i class="fa fa-plus"></i>
                                          <i class="fa fa-minus"></i>
                                          </span>
                                          </button>
                                       </div>
                                       <div id="collapseThree" class="collapse" data-parent="#accordion">
                                          <div class="card-body">
                                             <ul>
                                                <li>Free Exchange Available.</li>
                                                <li>Request an exchange within 7 days of delivery.</li>
                                                {{-- 
                                                <li>Click here to exchange your product.(Link Paste here)</li>
                                                --}}
                                                <li>Exchange is not applicable on free products or promotional items.</li>
                                                {{-- <li>Size and color exchanges are subject to stock availability.</li> --}}
                                                <li>Only one-time exchange is allowed per order.</li>
                                                {{-- <li>If you receive a wrong product, please share an unboxing video of the package for verification.</li> --}}
                                                <li>If you receive a wrong product, please share an images of the Product for verification.</li>
                                             </ul>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- Item 4 -->
                                    <div class="card">
                                       <div class="card-header">
                                          <button 
                                             class="btn btn-link collapsed" 
                                             data-toggle="collapse" 
                                             data-target="#collapseFour">
                                          <i class="fa fa-refresh accordion-left-icon"></i>
                                          7-Day Easy Return & Refund
                                          <span class="plus-minus">
                                          <i class="fa fa-plus"></i>
                                          <i class="fa fa-minus"></i>
                                          </span>
                                          </button>
                                       </div>
                                       <div id="collapseFour" class="collapse" data-parent="#accordion">
                                          <div class="card-body">
                                             <ul>
                                                <li>We offer a 7-day return policy for eligible products.</li>
                                                {{-- 
                                                <li>Click here to initiate your return request(Paste Return Link)</li>
                                                --}}
                                                <li>To be eligible for a return, the product must be unused and in its original condition and packaging.</li>
                                                <li>Returns cannot be initiated before the product is delivered.</li>
                                                <li>Refunds will be processed to the original payment method after the returned item is received and verified.</li>
                                                <li>For Cash on Delivery (COD) orders, customers will be required to provide bank account details for refund processing.</li>
                                                <li>Refunds are usually initiated within 2–4 working days after receiving the returned product.</li>
                                                <li>If you receive a wrong product, please share an images of the Product for verification purposes.</li>
                                                <li>In case your pin code is not serviceable for reverse pickup, you may be required to self-ship the product.</li>
                                                <li>Please do not accept the package if it appears tampered with or damaged.</li>
                                                <li>Do not share the OTP (One-Time Password) with the delivery partner unless you have received the package</li>
                                                {{-- <li>To cancel an order, please contact us through the “Contact Us” page. Our team will respond within 24 hours.</li> --}}
                                             </ul>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- Item 5 -->
                                    <div class="card">
                                       <div class="card-header">
                                          <button 
                                             class="btn btn-link collapsed" 
                                             data-toggle="collapse" 
                                             data-target="#collapseFive">
                                          <i class="fa fa-tint accordion-left-icon"></i>
                                          Wash & Care Instructions
                                          <span class="plus-minus">
                                          <i class="fa fa-plus"></i>
                                          <i class="fa fa-minus"></i>
                                          </span>
                                          </button>
                                       </div>
                                       <div id="collapseFive" class="collapse" data-parent="#accordion">
                                          <div class="card-body">
                                             <ul>
                                                <li>Clean the product using running water.</li>
                                                <li>Do not iron the garment.</li>
                                                <li>Do not tumble dry.</li>
                                             </ul>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- Item 6 -->
                                    <div class="card">
                                       <div class="card-header">
                                          <button 
                                             class="btn btn-link collapsed" 
                                             data-toggle="collapse" 
                                             data-target="#collapseSix">
                                          <i class="fa fa-globe accordion-left-icon"></i>
                                          Country Origin
                                          <span class="plus-minus">
                                          <i class="fa fa-plus"></i>
                                          <i class="fa fa-minus"></i>
                                          </span>
                                          </button>
                                       </div>
                                       <div id="collapseSix" class="collapse" data-parent="#accordion">
                                          <div class="card-body">
                                             <ul>
                                             <li>India</li>
                                          </div>
                                       </div>
                                    </div>
                                    <!-- Item 7 -->
                                    <div class="card">
                                       <div class="card-header">
                                          <button 
                                             class="btn btn-link collapsed" 
                                             data-toggle="collapse" 
                                             data-target="#collapseSeven">
                                          <i class="fa fa-industry accordion-left-icon"></i>
                                          Manufactured and Marketed By
                                          <span class="plus-minus">
                                          <i class="fa fa-plus"></i>
                                          <i class="fa fa-minus"></i>
                                          </span>
                                          </button>
                                       </div>
                                       <div id="collapseSeven" class="collapse" data-parent="#accordion">
                                          <div class="card-body">
                                             <ul>
                                                <li>NEW AASHI RAINWEAR, 843/2, NIDHI IND ESTATE, RAKANPUR, Gandhinagar, Gujarat, 382721</li>
                                             </ul>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 @endif
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="tab-pane fade" id="reviews" role="tabpanel">
                     <div class="tab-single review-panel">
                        <div class="row">
                           <div class="col-12">
                              <div class="ratting-main">
                                 <div class="avg-ratting">
                                    <h4>{{ceil($product_detail->getReview->avg('rate'))}} <span>(Overall)</span></h4>
                                    <span>Based on {{$product_detail->getReview->count()}} Comments</span>
                                 </div>
                                 @foreach($product_detail['getReview'] as $data)
                                 <div class="single-rating">
                                    <div class="rating-author">
                                       @if($data->user_info['photo'])
                                       <img src="{{asset('public/'.$data->user_info['photo'])}}" alt="{{$data->user_info['photo']}}">
                                       @else 
                                       <img src="{{asset('public/backend/img/avatar.png')}}" alt="Profile.jpg">
                                       @endif
                                    </div>
                                    <div class="rating-des">
                                       <h6>{{$data->user_info['name']}}</h6>
                                       <div class="ratings">
                                          <ul class="rating">
                                             @for($i=1; $i<=5; $i++)
                                             @if($data->rate>=$i)
                                             <li><i class="fa fa-star"></i></li>
                                             @else 
                                             <li><i class="fa fa-star-o"></i></li>
                                             @endif
                                             @endfor
                                          </ul>
                                          <div class="rate-count">(<span>{{$data->rate}}</span>)</div>
                                       </div>
                                       <p>{{$data->review}}</p>
                                    </div>
                                 </div>
                                 @endforeach
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
{{-- REVIEW SECTION --}}
@auth
@if($canReview == true && $hasReviewed == false)
{{-- 
<div class="container">
   <div class="row">
      <div class="col-12">
         <div class="comment-review">
            <div class="add-review">
               <h5>Add A Review</h5>
               <p>Your email address will not be published. Required fields are marked</p>
            </div>
            <h6>Your Rating <span class="text-danger">*</span></h6>
            <div class="review-inner">
               @auth
               <form class="form" method="post" action="{{route('review.store',$product_detail->slug)}}">
                  @csrf
                  <div class="row">
                     <div class="col-lg-12 col-12">
                        <input type="hidden" name="order_id" value="{{ $orderId }}">
                        <div class="rating_box">
                           <div class="star-rating">
                              <div class="star-rating__wrap">
                                 <input class="star-rating__input" id="star-rating-5" type="radio" name="rate" value="5">
                                 <label class="star-rating__ico fa fa-star-o" for="star-rating-5" title="5 out of 5 stars"></label>
                                 <input class="star-rating__input" id="star-rating-4" type="radio" name="rate" value="4">
                                 <label class="star-rating__ico fa fa-star-o" for="star-rating-4" title="4 out of 5 stars"></label>
                                 <input class="star-rating__input" id="star-rating-3" type="radio" name="rate" value="3">
                                 <label class="star-rating__ico fa fa-star-o" for="star-rating-3" title="3 out of 5 stars"></label>
                                 <input class="star-rating__input" id="star-rating-2" type="radio" name="rate" value="2">
                                 <label class="star-rating__ico fa fa-star-o" for="star-rating-2" title="2 out of 5 stars"></label>
                                 <input class="star-rating__input" id="star-rating-1" type="radio" name="rate" value="1">
                                 <label class="star-rating__ico fa fa-star-o" for="star-rating-1" title="1 out of 5 stars"></label>
                                 @error('rate')
                                 <span class="text-danger">{{$message}}</span>
                                 @enderror
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-lg-12 col-12">
                        <div class="form-group">
                           <label>Write a review</label>
                           <textarea name="review" rows="6" placeholder="" ></textarea>
                        </div>
                     </div>
                     <div class="col-lg-12 col-12">
                        <div class="form-group button5">	
                           <button type="submit" class="btn">Submit</button>
                        </div>
                     </div>
                  </div>
               </form>
               @else 
               <p class="text-center p-5">
                  You need to <a href="{{route('login.form')}}" style="color:rgb(54, 54, 204)">Login</a> OR <a style="color:blue" href="{{route('register.form')}}">Register</a>
               </p>
               @endauth
            </div>
         </div>
      </div>
   </div>
</div>
--}}
@endif
@endauth
@if(!empty($product_detail->rel_prods) && $product_detail->rel_prods->count() > 1)	
<div class="product-area most-popular related-product section">
   <div class="container">
      <div class="row">
         <div class="col-12">
            <div class="section-title">
               <h2>Related Products</h2>
            </div>
         </div>
      </div>
      <div class="row">
         {{-- {{$product_detail->rel_prods}} --}}
         <div class="col-12">
            <div class="owl-carousel popular-slider">
               @foreach($product_detail->rel_prods as $data)
               @if($data->id !==$product_detail->id)
               <!-- Start Single Product -->
               <div class="single-product">
                  <div class="product-img">
                     <a href="{{route('product-detail',$data->slug)}}">
                     @php 
                     $photo=explode(',',$data->photo);
                     @endphp
                     <img class="default-img" src="{{asset('public/'.$photo[0])}}" alt="{{asset('public/'.$photo[0])}}">
                     <img class="hover-img" src="{{asset('public/'.$photo[0])}}" alt="{{asset('public/'.$photo[0])}}">
                     <span class="price-dec">{{$data->discount}} % Off</span>
                     {{-- <span class="out-of-stock">Hot</span> --}}
                     </a>
                     <div class="button-head">
                        <div class="product-action">
                           <a data-toggle="modal" data-target="#modelExample" title="Quick View" href="#"><i class=" ti-eye"></i><span>Quick Shop</span></a>
                           <a title="Wishlist" href="#"><i class=" ti-heart "></i><span>Add to Wishlist</span></a>
                           <a title="Compare" href="#"><i class="ti-bar-chart-alt"></i><span>Add to Compare</span></a>
                        </div>
                        <div class="product-action-2">
                           <a title="Add to cart" href="#">Add to cart</a>
                        </div>
                     </div>
                  </div>
                  <div class="product-content">
                     <h3><a href="{{route('product-detail',$data->slug)}}">{{$data->product_code }}</a></h3>
                     <div class="product-price">
                        @php 
                        //$after_discount=($data->price-(($data->discount*$data->price)/100));
                        $sizes = json_decode($data->size);
                        $priceArr = $sizes->price;
                        $productPrice = 0;
                        foreach($priceArr as $k => $v){
                        $productPrice = $v;
                        }
                        $after_discount=($productPrice-($productPrice*$data->discount)/100);
                        $sizeData = json_decode($data->size, true);
                        @endphp
                        @if(isset($data->discount) && $data->discount > 0)
                        <span class="old">
                        @if(isset($data->price) && $data->price != null)
                        ₹{{number_format($data->price,2)}}
                        @else
                        ₹{{number_format($sizeData['price'][0],2)}}
                        @endif
                        </span>
                        <span>₹{{number_format($after_discount,2)}}</span>
                        @else
                        <span>
                        @if(isset($data->price) && $data->price != null)
                        ₹{{number_format($data->price,2)}}
                        @else
                        ₹{{number_format($sizeData['price'][0],2)}}
                        @endif
                        </span>
                        @endif
                     </div>
                  </div>
               </div>
               <!-- End Single Product -->
               @endif
               @endforeach
            </div>
         </div>
      </div>
   </div>
</div>
@endif
<div class="modal fade" id="modelExample" tabindex="-1" role="dialog">
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
                        <div class="single-slider">
                           <img src="images/modal1.png" alt="#">
                        </div>
                        <div class="single-slider">
                           <img src="images/modal2.png" alt="#">
                        </div>
                        <div class="single-slider">
                           <img src="images/modal3.png" alt="#">
                        </div>
                        <div class="single-slider">
                           <img src="images/modal4.png" alt="#">
                        </div>
                     </div>
                  </div>
                  <!-- End Product slider -->
               </div>
               <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                  <div class="quickview-content">
                     <h2>Flared Shift Dress</h2>
                     <div class="quickview-ratting-review">
                        <div class="quickview-ratting-wrap">
                           <div class="quickview-ratting">
                              <i class="yellow fa fa-star"></i>
                              <i class="yellow fa fa-star"></i>
                              <i class="yellow fa fa-star"></i>
                              <i class="yellow fa fa-star"></i>
                              <i class="fa fa-star"></i>
                           </div>
                           <a href="#"> (1 customer review)</a>
                        </div>
                        <div class="quickview-stock">
                           <span><i class="fa fa-check-circle-o"></i> in stock</span>
                        </div>
                     </div>
                     <h3>$29.00</h3>
                     <div class="quickview-peragraph">
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Mollitia iste laborum ad impedit pariatur esse optio tempora sint ullam autem deleniti nam in quos qui nemo ipsum numquam.</p>
                     </div>
                     <div class="size">
                        <div class="row">
                           <div class="col-lg-6 col-12">
                              <h5 class="title">Size</h5>
                              <select>
                                 <option selected="selected">s</option>
                                 <option>m</option>
                                 <option>l</option>
                                 <option>xl</option>
                              </select>
                           </div>
                           <div class="col-lg-6 col-12">
                              <h5 class="title">Color</h5>
                              <select>
                                 <option selected="selected">orange</option>
                                 <option>purple</option>
                                 <option>black</option>
                                 <option>pink</option>
                              </select>
                           </div>
                        </div>
                     </div>
                     <div class="quantity">
                        <!-- Input Order -->
                        <div class="input-group">
                           <div class="button minus">
                              <button type="button" class="btn btn-primary btn-number" disabled="disabled" data-type="minus" data-field="quant[1]">
                              <i class="ti-minus"></i>
                              </button>
                           </div>
                           <input type="text" name="qty" class="input-number"  data-min="1" data-max="1000" value="1">
                           <div class="button plus">
                              <button type="button" class="btn btn-primary btn-number" data-type="plus" data-field="quant[1]">
                              <i class="ti-plus"></i>
                              </button>
                           </div>
                        </div>
                        <!--/ End Input Order -->
                     </div>
                     <div class="add-to-cart">
                        <a href="#" class="btn">Add to cart</a>
                        <a href="#" class="btn min"><i class="ti-heart"></i></a>
                        <a href="#" class="btn min"><i class="fa fa-compress"></i></a>
                     </div>
                     <div class="default-social">
                        <h4 class="share-now">Share:</h4>
                        <ul>
                           <li><a class="facebook" href="#"><i class="fa fa-facebook"></i></a></li>
                           <li><a class="twitter" href="#"><i class="fa fa-twitter"></i></a></li>
                           <li><a class="youtube" href="#"><i class="fa fa-pinterest-p"></i></a></li>
                           <li><a class="dribbble" href="#"><i class="fa fa-google-plus"></i></a></li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- Modal end -->


@if($product_detail->size_chart)
<div class="modal fade size-chart-modal" id="sizeChartModal" tabindex="-1" role="dialog"
    aria-labelledby="sizeChartModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <!--<h5 class="modal-title" id="sizeChartModalLabel">-->
                <!--    Size Chart-->
                <!--</h5>-->

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <!--<div class="table-responsive">-->
                    {!! $product_detail->size_chart !!}
                <!--</div>-->
            </div>

        </div>
    </div>
</div>
@endif

@endsection
@push('styles')
<style>



/*modal css start*/
/* Modal Width */
#sizeChartModal .modal-dialog {
    max-width: 900px;
    width: 95%;
    margin: 1.75rem auto;
}

/* Modal Box */
#sizeChartModal .modal-content {
    position: relative;
    border: 0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
}

/* Header */
#sizeChartModal .modal-header {
    padding: 0px 10px;
    /*border-bottom: 1px solid #ececec;*/
    justify-content: flex-end;
}

/* Close Button */
#sizeChartModal .close {
    margin: 0;
    padding: 0;
    width: 36px;
    height: 36px;
    border: 0;
    background: #f5f5f5;
    border-radius: 50%;
    font-size: 28px;
    line-height: 36px;
    text-align: center;
    opacity: 1;
    outline: none;
    box-shadow: none;
    transition: all 0.3s ease;
}

#sizeChartModal .close:hover {
    background: #e9e9e9;
}

/* Body */
#sizeChartModal .modal-body {
    padding: 25px;
    max-height: 85vh;
    overflow-y: auto;
}

/* Headings */
#sizeChartModal h4,
#sizeChartModal h5 {
    margin: 0 0 20px;
    font-weight: 700;
    color: #222;
    line-height: 1.4;
}

#sizeChartModal h4 {
    font-size: 28px;
}

#sizeChartModal h5 {
    font-size: 24px;
}

/* Table */
#sizeChartModal table {
    width: 100%;
    margin-bottom: 30px;
    border-collapse: collapse;
}

#sizeChartModal table th,
#sizeChartModal table td {
    padding: 14px 16px;
    text-align: center;
    border: 1px solid #e5e5e5;
    vertical-align: middle;
}

#sizeChartModal table th {
    background: #f8f8f8;
    font-weight: 600;
}

/* List */
#sizeChartModal ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

#sizeChartModal ul li {
    position: relative;
    padding-left: 20px;
    margin-bottom: 12px;
    line-height: 1.6;
    color: #444;
}

#sizeChartModal ul li:last-child {
    margin-bottom: 0;
}

#sizeChartModal ul li::before {
    content: "";
    position: absolute;
    left: 0;
    top: 10px;
    width: 7px;
    height: 7px;
    background: #5db845;
    border-radius: 50%;
}

/* Mobile */
@media (max-width: 767px) {
    
    #sizeChartModal .modal-header
    {
        padding:0px;
    }
    #sizeChartModal .modal-dialog {
        width: calc(100% - 20px);
        margin: 10px;
    }

    #sizeChartModal .modal-body {
        padding: 20px 15px;
    }

    #sizeChartModal h4 {
        font-size: 22px;
    }

    #sizeChartModal h5 {
        font-size: 20px;
    }

    #sizeChartModal table th,
    #sizeChartModal table td {
        padding: 10px;
        font-size: 14px;
        white-space: nowrap;
    }

    #sizeChartModal ul li {
        font-size: 14px;
        line-height: 1.5;
    }
}

/*modal css end*/

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
   /* for share button css */
   .product-share{
   display:flex;
   align-items:center;
   gap:10px;
   flex-wrap:wrap;
   }
   .product-share h6{
   margin:0;
   font-size:15px;
   font-weight:600;
   }
   .share-btn{
   width:40px;
   height:40px;
   border-radius:50%;
   display:flex;
   align-items:center;
   justify-content:center;
   color:#fff !important;
   font-size:18px;
   transition:0.3s;
   text-decoration:none;
   }
   .share-btn:hover{
   transform:translateY(-3px);
   }
   .share-btn.whatsapp{
   background:#25D366;
   }
   .share-btn.facebook{
   background:#1877F2;
   }
   .share-btn.instagram{
   background:#E4405F;
   }
   
   .View-Size-Chart
   {
       border-bottom:1px solid  rgb(93, 184, 69);
       margin-top:10px;
       color: #333;
   }
   
</style>
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<script>
   function setPriceId(element) {
   	// console.log(element);
   	var priceId = element.getAttribute('data-price-id');
   	var sizeId = element.getAttribute('data-size-id');
   	var colorId = element.getAttribute('data-color-id');
   	var colorName = element.getAttribute('data-color-name');
   	
   	var selectedPrice = document.getElementById('selected_price').value;
   	var selectedSize = document.getElementById('selected_size').value;
   	var selectedColor = document.getElementById('selected_color').value;
   	var selectedColorName = document.getElementById('selected_color_name').value;
   
   	// Set the selected size and price in hidden inputs	
   	document.getElementById('selected_size').value = sizeId != null ? sizeId : selectedSize;
   	document.getElementById('selected_price').value = priceId != null ? priceId : selectedPrice;
   	document.getElementById('selected_color').value = colorId != null ? colorId : selectedColor;
   	document.getElementById('selected_color_name').value = colorName != null ? colorName : selectedColorName;
   
   	if(priceId == null){
   		priceId = document.getElementById('selected_price').value;
   	}
   	
   	// const targetDiv = document.getElementById('targetDivId');
   	// targetDiv.textContent = '₹'+priceId+'.00';
   	const priceWrapper = document.querySelector('.price');
   	const discountPercent = parseFloat(priceWrapper.dataset.discount || 0);
   	const basePrice = parseFloat(priceId);
   	let finalPrice = basePrice;
   
   	if (discountPercent > 0) {
   		finalPrice = basePrice - (basePrice * discountPercent / 100);
   		// Update strike price
   		const originalPriceEl = priceWrapper.querySelector('.original-price del');
   		if (originalPriceEl) {
   			originalPriceEl.innerText = '₹' + basePrice.toFixed(2);
   		}
   	}
   	// Update discounted / final price
   	document.getElementById('targetDivId').innerText =
   		'₹' + finalPrice.toFixed(2);
   	// Update hidden input (important for cart)
   	document.getElementById('selected_price').value = finalPrice.toFixed(2);
   
   
   	const links = document.querySelectorAll('.one');
   	links.forEach(link => {
   		link.classList.remove('set_active');
   	});
   	element.classList.add('set_active');
   	checkWishlistStatus();
   }
   $(document).ready(function() {
   	checkWishlistStatus();
      // When a size is clicked
      // $('#size-options a').click(function(e) {
      //     e.preventDefault(); // Prevent default action of the link
   
      //     // Remove 'set_active' from all links and add to the clicked one
      //     $('#size-options a').removeClass('set_active');
      //     $(this).addClass('set_active');
   
      //     // Get the price associated with the clicked size
      //     var price = $(this).data('price');
   
      //     // Update the price display
      //     $('#price-display').text(parseFloat(price).toFixed(2));
      // });
   
   $('.add-to-wishlist').on('click', function (e) {
   	e.preventDefault();
   
   	let baseUrl = "{{ route('add-to-wishlist', $product_detail->slug) }}";
   	let colorId = $('#selected_color').val();
   
   	window.location.href = baseUrl + '?color_id=' + colorId;
   });

   $('.buy-now-btn').on('click', function (e) {
   	e.preventDefault();

   	let baseUrl = "{{ route('add-to-cart', ['slug' => $product_detail->slug, 'buy_now' => 'buyNow']) }}";
   	let colorId = $('#selected_color').val();

   	window.location.href = baseUrl + '&color_id=' + colorId;
   });
   });
   
   function checkWishlistStatus() {
      let productId = "{{ $product_detail->id }}";
      let colorId = $('#selected_color').val();
   
      $.ajax({
          url: "{{ route('wishlist.check') }}",
          type: "GET",
          data: {
              product_id: productId,
              color_id: colorId
          },
          success: function (res) {
   		console.log("RES - "+ JSON.stringify(res));
              if (res.wishlisted) {
                  $('.add-to-wishlist').addClass('active');
              } else {
                  $('.add-to-wishlist').removeClass('active');
              }
          }
      });
   }
   
      $(document).ready(function() {
      // Show the first color's thumbnails and main image by default
      var defaultColorId = $('#color-options .color-selector').first().data('color-id');
   
      // Handle color selection
      $('.color-selector').on('click', function(e) {
          e.preventDefault(); // Prevent default anchor behavior
   
          // Remove 'selected' class from all color icons
          $('#color-options i').removeClass('selected');
   
          // Add 'selected' class to the clicked color icon
          $(this).find('i').addClass('selected');
   
          // Get the color ID of the selected color
          var colorId = $(this).data('color-id');
   
          // Fetch thumbnails and main image for the selected color
          fetchColorImages(colorId);
      });
   
      // On thumbnail click, update the main image
      $(document).on('click', '.thumbnail', function() {
          var newImageSrc = $(this).data('image');
          $('#main-img').attr('src', newImageSrc); // Update main image
      });
   
      // Function to fetch and update the image list based on color ID
      function fetchColorImages(colorId) {
      // Make an AJAX request to fetch the images for the selected color
   var fetchUrl = '{{ url("get-color-images") }}' + '/' + colorId;
      $.ajax({
          url: fetchUrl,
          type: 'GET',
          success: function(response) {
              // Clear existing sliders (both main and small banners)
              $('.main-banner').slick('unslick');  // Uninitialize Slick before updating
              $('.small-banner').slick('unslick'); // Uninitialize Slick before updating
              $('.main-banner').empty();           // Clear the current images
              $('.small-banner').empty();          // Clear the thumbnails
   
              // Append new images to the main banner
              $.each(response.images, function(index, image) {
                  $('.main-banner').append(
                      '<div><img src="' + image + '" alt="Main Image ' + (index + 1) + '"></div>'
                  );
              });
   
              // Append new images to the thumbnail slider
              $.each(response.images, function(index, image) {
                  $('.small-banner').append(
                      '<div><img src="' + image + '" alt="Thumbnail Image ' + (index + 1) + '"></div>'
                  );
              });
   
              // Reinitialize Slick for both sliders after appending new images
              $('.main-banner').slick({
                  slidesToShow: 1,
                  slidesToScroll: 1,
                  arrows: false,
                  fade: true,
                  asNavFor: '.small-banner'
              });
   
              $('.small-banner').slick({
                  slidesToShow: 3,
                  slidesToScroll: 1,
                  asNavFor: '.main-banner',
                  dots: true,
                  centerMode: true,
                  focusOnSelect: true,
   			arrows:true
              });
          },
          error: function(xhr) {
              console.error("Error fetching color images: ", xhr);
          }
      });
   }
   
   });
   
   
   
</script>
<!-- Slick JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel/slick/slick.min.js"></script>
<script type="text/javascript">
   $(document).ready(function() {
    $('.main-banner').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: false,
        asNavFor: '.small-banner' // This links the main banner with the thumbnail banner
    });
   
    $('.small-banner').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        asNavFor: '.main-banner', 
        focusOnSelect: true,
   arrows:true,
    });
   });
   
</script>
@endpush
