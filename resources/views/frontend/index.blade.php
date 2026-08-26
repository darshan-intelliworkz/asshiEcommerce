@extends('frontend.layouts.master')
@section('title','Aashi-Ecommerce || HOME PAGE')
@section('main-content')
<!-- Slider Area -->
@if(!empty($banners) && count($banners) > 0)
    <section id="Gslider" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            @foreach($banners as $key=>$banner)
        <li data-target="#Gslider" data-slide-to="{{$key}}" class="{{(($key==0)? 'active' : '')}}"></li>
            @endforeach

        </ol>
        <div class="carousel-inner" role="listbox">
                @foreach($banners as $key=>$banner)
                <div class="carousel-item {{(($key==0)? 'active' : '')}}">
                    <img class="first-slide" src="{{asset('public/'.$banner->photo)}}" alt="First slide">
                   
                </div>
            @endforeach
        </div>
        <a class="carousel-control-prev" href="#Gslider" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#Gslider" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
        </a>
    </section>
@endif

<!--/ End Slider Area -->

<!-- Start Small Banner  -->
<section class="small-banner section">
    <div class="container-fluid">
        <div class="row justify-content-center">
            @php
            $category_lists=DB::table('categories')->where('status','active')->limit(3)->get();
            @endphp
            @if($category_lists)
                @foreach($category_lists as $cat)
                    @if($cat->is_parent==1)
                        <!-- Single Banner  -->
                        <div class="col-lg-4 col-md-6 col-12 mb-4">
                            <div class="single-banner">
                                @if($cat->photo)
                                    <img src="{{asset('public/'.$cat->photo)}}" alt="{{asset('public/'.$cat->photo)}}">
                                @else
                                    <img src="https://via.placeholder.com/600x370" alt="#">
                                @endif
                                <div class="content">
                                    <h3>{{$cat->title}}</h3>
                                        <a href="{{route('product-cat',$cat->slug)}}">Discover Now</a>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!-- /End Single Banner  -->
                @endforeach
            @endif
        </div>
    </div>
</section>
<!-- End Small Banner -->

<!-- Start Product Area -->
<div class="product-area section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-title">
                        <h2>Trending Item</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="product-info">
                        <div class="nav-main text-center mb-5">
                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs justify-content-center" id="productTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="btn filter-btn active" id="all-products-tab" data-toggle="tab" href="#all-products" role="tab">
                                        ALL PRODUCTS
                                    </a>
                                </li>
                                @php
                                    $categories = DB::table('categories')->where('status', 'active')->where('is_parent', 1)->get();
                                @endphp
                                @foreach($categories as $category)
                                    <li class="nav-item">
                                        <a class="btn filter-btn" id="category-{{$category->id}}-tab" data-toggle="tab" href="#category-{{$category->id}}" role="tab">
                                            {{ strtoupper($category->title) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>



                        <!-- Tab Content -->
                        <div class="tab-content" id="productTabContent">
                            <!-- All Products Tab -->
                            <div class="tab-pane fade show active" id="all-products" role="tabpanel">
                                <div class="row">
                                    @foreach($product_lists as $product)
                                        <div class="col-6 col-md-4 col-lg-3 d-flex align-items-stretch">
                                            @include('frontend.layouts._trending_card', ['product' => $product])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        
                            <!-- Category Specific Products -->
                            @foreach($categories as $category)
                                @php
                                    $categoryProducts = \App\Models\Product::with(['cat_info', 'getReview'])
                                        ->where('status', 'active')
                                        ->where('cat_id', $category->id)
                                        ->whereNull('deleted_at')
                                        ->where('is_featured', 1)
                                        ->whereHas('cat_info', function ($query) {
                                            $query->where('status', 'active');
                                        })
                                        ->orderBy('id', 'DESC')
                                        ->limit(8)
                                        ->get();
                                @endphp

                                <div class="tab-pane fade" id="category-{{$category->id}}" role="tabpanel">
                                    <div class="row">
                                        @foreach($categoryProducts as $product)
                                            <div class="col-6 col-md-4 col-lg-3 d-flex align-items-stretch">
                                                @include('frontend.layouts._trending_card', ['product' => $product])
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </div>
</div>
<!-- End Product Area -->
<!-- Start Midium Banner  -->
<section class="midium-banner">
    <div class="container">
        <div class="row">
            @if($featured)
                @foreach($featured as $data)
                    <!-- Single Banner  -->
                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="single-banner">
                            @php
                                $photo=explode(',',$data->photo);
                            @endphp
                            <img src="{{asset('public/'.$photo[0])}}" alt="{{asset('public/'.$photo[0])}}">
                            <div class="content">
                                <p>{{$data->cat_info['title']}}</p>
                                <h3>{{$data->title}}</h3>
                                <a href="{{route('product-cat',$data->cat_info['slug'])}}">Shop Now</a>
                            </div>
                        </div>
                    </div>
                    <!-- /End Single Banner  -->
                @endforeach
            @endif
        </div>
    </div>
</section>
<!-- End Midium Banner -->

<!-- Start Most Popular -->
<div class="product-area most-popular section" style="padding: 50px 0 30px 0;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h2>Brands</h2>
                </div>
            </div>
        </div>
        <div class="row text-center justify-content-center">
            <div class="col-lg-3 col-md-4 mb-3 brand_mg">
                <img src="{{asset('public/brand/aashi-logo.png')}}" alt="" class="img-fluid">
            </div>
            <div class="col-lg-3 col-md-4 mb-3 brand_mg">
                <img src="{{asset('public/brand/Aone-logo.jpg')}}" alt="" class="img-fluid">
            </div>
            <div class="col-lg-3 col-md-4 mb-3 brand_mg">
                <img src="{{asset('public/brand/kiash-logo.jpg')}}" alt="" class="img-fluid">
            </div>
            <div class="col-lg-3 col-md-4 mb-3 brand_mg">
                <img src="{{asset('public/brand/Rainshell-Logo.jpeg')}}" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>
<!-- End Most Popular Area -->

<!-- Start Shop Blog  -->
<!--<section class="shop-blog section">-->
<!--    <div class="container">-->
<!--        <div class="row">-->
<!--            <div class="col-12">-->
<!--                <div class="section-title">-->
<!--                    <h2>From Our Blog</h2>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="row">-->
<!--            @if($posts)-->
<!--                @foreach($posts as $post)-->
<!--                    <div class="col-lg-4 col-md-6 col-12">-->
                        <!-- Start Single Blog  -->
<!--                        <div class="shop-single-blog">-->
<!--                            <img src="{{asset('public/'.$post->photo)}}" alt="{{asset('public/'.$post->photo)}}">-->
<!--                            <div class="content">-->
<!--                                <p class="date">{{$post->created_at->format('d M , Y. D')}}</p>-->
<!--                                <a href="{{route('blog.detail',$post->slug)}}" class="title">{{$post->title}}</a>-->
<!--                                <a href="{{route('blog.detail',$post->slug)}}" class="more-btn">Continue Reading</a>-->
<!--                            </div>-->
<!--                        </div>-->
                        <!-- End Single Blog  -->
<!--                    </div>-->
<!--                @endforeach-->
<!--            @endif-->

<!--        </div>-->
<!--    </div>-->
<!--</section>-->
<!-- End Shop Blog  -->

<!-- Start Shop Services Area -->
<!--<section class="shop-services section home">-->
<!--    <div class="container">-->
<!--        <div class="row">-->
<!--            <div class="col-lg-3 col-md-6 col-12">-->
                <!-- Start Single Service -->
<!--                <div class="single-service">-->
<!--                    <i class="ti-rocket"></i>-->
<!--                    <h4>Free shiping</h4>-->
<!--                    <p>Orders over $100</p>-->
<!--                </div>-->
                <!-- End Single Service -->
<!--            </div>-->
<!--            <div class="col-lg-3 col-md-6 col-12">-->
                <!-- Start Single Service -->
<!--                <div class="single-service">-->
<!--                    <i class="ti-reload"></i>-->
<!--                    <h4>Free Return</h4>-->
<!--                    <p>Within 30 days returns</p>-->
<!--                </div>-->
                <!-- End Single Service -->
<!--            </div>-->
<!--            <div class="col-lg-3 col-md-6 col-12">-->
                <!-- Start Single Service -->
<!--                <div class="single-service">-->
<!--                    <i class="ti-lock"></i>-->
<!--                    <h4>Sucure Payment</h4>-->
<!--                    <p>100% secure payment</p>-->
<!--                </div>-->
                <!-- End Single Service -->
<!--            </div>-->
<!--            <div class="col-lg-3 col-md-6 col-12">-->
                <!-- Start Single Service -->
<!--                <div class="single-service">-->
<!--                    <i class="ti-tag"></i>-->
<!--                    <h4>Best Peice</h4>-->
<!--                    <p>Guaranteed price</p>-->
<!--                </div>-->
                <!-- End Single Service -->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->
<!-- End Shop Services Area -->


<!-- Modal -->
@php
    $modalProducts = collect($product_lists);
    if(isset($categories)) {
        foreach($categories as $cat) {
            $catProds = \App\Models\Product::where('status', 'active')
                ->where('cat_id', $cat->id)
                ->whereNull('deleted_at')
                ->where('is_featured', 1)
                ->limit(8)
                ->get();
            $modalProducts = $modalProducts->concat($catProds);
        }
    }
    $uniqueModalProducts = $modalProducts->unique('id');
@endphp

@if($uniqueModalProducts && count($uniqueModalProducts) > 0)
    @foreach($uniqueModalProducts as $key=>$product)
        @php
            $m_sizeData = null;
            $m_productPrice = 0;
            if(!empty($product->size)){
                $m_decoded = json_decode($product->size, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($m_decoded) && isset($m_decoded['price']) && is_array($m_decoded['price']) && count($m_decoded['price']) > 0) {
                    $m_sizeData = $m_decoded;
                    $m_productPrice = floatval($m_decoded['price'][0] ?? 0);
                }
            }
            if(!$m_productPrice && isset($product->price) && is_numeric($product->price)){
                $m_productPrice = floatval($product->price);
            }
            $m_discount = isset($product->discount) && is_numeric($product->discount) ? floatval($product->discount) : 0;
            $m_afterDiscount = ($m_discount > 0 && $m_productPrice > 0) ? ($m_productPrice - (($m_productPrice * $m_discount) / 100)) : $m_productPrice;
        @endphp
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
                                                @endphp
                                                @foreach($photo as $data)
                                                    <div class="single-slider">
                                                        <img src="{{asset('public/'.$data)}}" alt="{{$product->title ?: $product->product_code}}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    <!-- End Product slider -->
                                </div>
                                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                                    <div class="quickview-content">
                                        <h2>{{$product->title ?: $product->product_code}}</h2>
                                        @if($product->product_code)
                                            <p class="text-muted small mb-2">Item Code: <strong>{{$product->product_code}}</strong></p>
                                        @endif
                                        <div class="quickview-ratting-review">
                                            <div class="quickview-ratting">
                                                @php
                                                    $rate=DB::table('product_reviews')->where('product_id',$product->id)->avg('rate');
                                                    $rate_count=DB::table('product_reviews')->where('product_id',$product->id)->count();
                                                @endphp
                                                @for($i=1; $i<=5; $i++)
                                                    @if($rate>=$i)
                                                        <i class="yellow fa fa-star"></i>
                                                    @else
                                                        <i class="fa fa-star text-muted"></i>
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
                                        <h3>
                                            @if($m_discount > 0 && $m_productPrice > $m_afterDiscount)
                                                <small><del class="text-muted">₹{{number_format($m_productPrice,2)}}</del></small>
                                            @endif
                                            ₹{{number_format($m_afterDiscount,2)}}
                                        </h3>
                                        <div class="quickview-peragraph">
                                            <p>{!! html_entity_decode($product->summary) !!}</p>
                                        </div>
                                        @if(!empty($m_sizeData) && isset($m_sizeData['size']) && count($m_sizeData['size']) > 0)
                                            <div class="size">
                                                <div class="row">
                                                    <div class="col-lg-12 col-12">
                                                        <h5 class="title">Available Sizes</h5>
                                                        <div class="d-flex flex-wrap gap-2 mt-1">
                                                            @foreach($m_sizeData['size'] as $size)
                                                                <span class="badge badge-light border mr-1 px-2 py-1">{{$size}}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <form action="{{route('single-add-to-cart')}}" method="POST" class="mt-4">
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
                                                <a href="{{route('add-to-wishlist',$product->slug)}}" class="btn min" title="Wishlist"><i class="ti-heart"></i></a>
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

@push('styles')
    <style>
        /* Banner Sliding */
        #Gslider .carousel-inner {
        background: #000000;
        color:black;
        }

        #Gslider .carousel-inner img{
            width: 100% !important;
            opacity: .8;
        }

        #Gslider .carousel-inner .carousel-caption {
        bottom: 60%;
        }

        #Gslider .carousel-inner .carousel-caption h1 {
        font-size: 50px;
        font-weight: bold;
        line-height: 100%;
        color: #F7941D;
        }

        #Gslider .carousel-inner .carousel-caption p {
        font-size: 18px;
        color: black;
        margin: 28px 0 28px 0;
        }

        #Gslider .carousel-indicators {
        bottom: 70px;
        }
        
        .nav-tabs {
            border-bottom: none;
        }

        .nav-tabs .nav-item {
            margin-right: 5px;
        }

        .nav-tabs .filter-btn {
            background: white;
            color: black;
            font-weight: bold;
            border: 1px solid #ddd;
            padding: 10px 20px;
            border-radius: 0;
            transition: all 0.3s ease-in-out;
        }

        /* Active Tab */
        .nav-tabs .filter-btn.active, 
        .nav-tabs .filter-btn:hover {
            background: black !important;
            color: white !important;
        }

        /* ==========================================================
           MODERN CUSTOM PRODUCT CARD STYLING
           ========================================================== */
        .custom-product-card {
            background: #ffffff;
            border: 1px solid #e8edf2;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            width: 100%;
            position: relative;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            margin-bottom: 28px;
        }

        .custom-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.09);
            border-color: #cbd5e1;
        }

        .card-media-wrap {
            position: relative;
            background: #f8fafc;
            height: 310px;
            width: 100%;
            overflow: hidden;
            border-bottom: 1px solid #f1f5f9;
        }

        .card-img-link {
            display: block;
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
            position: relative;
        }

        .card-default-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top center;
            display: block;
            transition: transform 0.4s ease, opacity 0.3s ease;
        }

        .card-hover-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top center;
            opacity: 0;
            display: block;
            transition: opacity 0.35s ease, transform 0.4s ease;
        }

        .custom-product-card:hover .card-default-img {
            transform: scale(1.05);
        }

        .custom-product-card:hover .card-hover-img {
            opacity: 1;
            transform: scale(1.05);
        }

        /* Left Badges on Media */
        .card-left-badges {
            position: absolute;
            top: 10px;
            left: 10px;
            display: flex;
            flex-direction: column;
            gap: 5px;
            z-index: 3;
        }

        .card-badge-tag {
            font-size: 10.5px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 0.4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
            text-transform: uppercase;
            line-height: 1.2;
        }

        .card-badge-tag.badge-hot {
            background: #e11d48;
            color: #ffffff;
        }

        .card-badge-tag.badge-hot i {
            font-size: 10px;
        }

        .card-badge-tag.badge-new {
            background: #0284c7;
            color: #ffffff;
        }

        .card-badge-tag.badge-discount {
            background: #f7941d;
            color: #ffffff;
        }

        .card-action-buttons {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            z-index: 3;
            opacity: 0;
            transform: translateX(8px);
            transition: all 0.25s ease;
        }

        .custom-product-card:hover .card-action-buttons {
            opacity: 1;
            transform: translateX(0);
        }

        .card-btn-action {
            width: 34px;
            height: 34px;
            background: #ffffff;
            color: #333333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
            font-size: 13px;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }

        .card-btn-action:hover {
            background: #333333;
            color: #ffffff !important;
            transform: scale(1.08);
        }

        .card-content-wrap {
            padding: 14px 16px 16px 16px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .card-meta-line {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .card-cat-name {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 50%;
        }

        .card-top-price {
            display: flex;
            align-items: baseline;
            gap: 5px;
        }

        .card-top-price .price-val {
            font-size: 15.5px;
            font-weight: 800;
            color: #0f172a;
        }

        .card-top-price .price-old {
            font-size: 11.5px;
            color: #94a3b8;
            text-decoration: line-through;
        }

        .card-item-title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            margin: 2px 0 10px 0;
            min-height: 38px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-item-title a {
            color: #1e293b;
            transition: color 0.2s ease;
            text-decoration: none !important;
        }

        .card-item-title a:hover {
            color: #f7941d;
        }

        .card-cta-container {
            margin-top: auto;
        }

        .btn-card-details {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 8px 12px;
            background: #111827;
            color: #ffffff !important;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none !important;
            transition: all 0.25s ease;
        }

        .btn-card-details:hover {
            background: #f7941d;
            box-shadow: 0 4px 12px rgba(247, 148, 29, 0.35);
        }

        .btn-card-details i {
            font-size: 11px;
            transition: transform 0.2s ease;
        }

        .btn-card-details:hover i {
            transform: translateX(4px);
        }

        @media (max-width: 991px) {
            .card-media-wrap {
                height: 260px;
            }
        }

        @media (max-width: 576px) {
            .card-media-wrap {
                height: 210px;
            }
            .card-content-wrap {
                padding: 10px;
            }
            .card-item-title {
                font-size: 13px;
                min-height: 34px;
            }
            .price-val {
                font-size: 15px;
            }
            .card-action-buttons {
                opacity: 1;
                transform: translateX(0);
            }
            .card-btn-action {
                width: 28px;
                height: 28px;
                font-size: 11px;
            }
            .btn-card-details {
                font-size: 11.5px;
                padding: 6px 8px;
            }
        }
    </style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script>
    
$(document).ready(function () {
    // $('.filter-btn').on('click', function () {
    //     var categoryId = $(this).data('filter'); 

    //     $.ajax({
    //         url: "{{ route('filter-products') }}",
    //         type: "GET",
    //         data: { category_id: categoryId },
    //         beforeSend: function () {
    //             $('#product-list').html('<p class="text-center">Loading...</p>'); 
    //         },
    //         success: function (response) {
    //             // If there is an error message in response
    //             if (response.error) {
    //                 $('#product-list').html('<p class="text-center alert alert-danger">' + response.error + '</p>'); 
    //             } else {
    //                 $('#product-list').html(response); // Replace with the filtered products
    //             }
    //         },
    //         error: function () {
    //             $('#product-list').html('<p class="text-center alert alert-danger">Something went wrong. Please try again.</p>'); 
    //         }
    //     });
    // });
});



        /*==================================================================
        [ Isotope ]*/
        var $topeContainer = $('.isotope-grid');
        var $filter = $('.filter-tope-group');

        // filter items on button click
        $filter.each(function () {
            $filter.on('click', 'button', function () {
                var filterValue = $(this).attr('data-filter');
                $topeContainer.isotope({filter: filterValue});
            });

        });

        // init Isotope
        $(window).on('load', function () {
            var $grid = $topeContainer.each(function () {
                $(this).isotope({
                    itemSelector: '.isotope-item',
                    layoutMode: 'fitRows',
                    percentPosition: true,
                    animationEngine : 'best-available',
                    masonry: {
                        columnWidth: '.isotope-item'
                    }
                });
            });
        });

        var isotopeButton = $('.filter-tope-group button');

        $(isotopeButton).each(function(){
            $(this).on('click', function(){
                for(var i=0; i<isotopeButton.length; i++) {
                    $(isotopeButton[i]).removeClass('how-active1');
                }

                $(this).addClass('how-active1');
            });
        });
    </script>
    <script>
         function cancelFullScreen(el) {
            var requestMethod = el.cancelFullScreen||el.webkitCancelFullScreen||el.mozCancelFullScreen||el.exitFullscreen;
            if (requestMethod) { // cancel full screen.
                requestMethod.call(el);
            } else if (typeof window.ActiveXObject !== "undefined") { // Older IE.
                var wscript = new ActiveXObject("WScript.Shell");
                if (wscript !== null) {
                    wscript.SendKeys("{F11}");
                }
            }
        }

        function requestFullScreen(el) {
            // Supports most browsers and their versions.
            var requestMethod = el.requestFullScreen || el.webkitRequestFullScreen || el.mozRequestFullScreen || el.msRequestFullscreen;

            if (requestMethod) { // Native full screen.
                requestMethod.call(el);
            } else if (typeof window.ActiveXObject !== "undefined") { // Older IE.
                var wscript = new ActiveXObject("WScript.Shell");
                if (wscript !== null) {
                    wscript.SendKeys("{F11}");
                }
            }
            return false
        }
    </script>

@endpush
