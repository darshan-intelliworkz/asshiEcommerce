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
                        <div class="nav-main text-center">
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
                                        <div class="col-sm-6 col-md-4 col-lg-3 p-b-35">
                                            <div class="single-product">
                                                <div class="product-img">
                                                    <a href="{{route('product-detail',$product->slug)}}">
                                                        @php $photo = explode(',', $product->photo); @endphp
                                                        <img class="default-img" src="{{asset('public/'.$photo[0])}}" alt="{{$product->title}}">
                                                    </a>
                                                </div>
                                                <div class="product-content text-center">
                                                    <h3><a href="{{route('product-detail',$product->slug)}}">{{$product->product_code}}</a></h3>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        
                            <!-- Category Specific Products -->
                            @foreach($categories as $category)
                                @php
                                    $categoryProducts = \App\Models\Product::where('status', 'active')
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
                                            <div class="col-sm-6 col-md-4 col-lg-3 p-b-35">
                                                <div class="single-product">
                                                    <div class="product-img">
                                                        <a href="{{route('product-detail',$product->slug)}}">
                                                            @php $photo = explode(',', $product->photo); @endphp
                                                            <img class="default-img" src="{{asset('public/'.$photo[0])}}" alt="{{$product->title}}">
                                                        </a>
                                                    </div>
                                                    <div class="product-content text-center">
                                                        <h3><a href="{{route('product-detail',$product->slug)}}">{{$product->product_code}}</a></h3>
                                                    </div>
                                                </div>
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
@if($product_lists)
    @foreach($product_lists as $key=>$product)
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
                                                    {{-- <i class="yellow fa fa-star"></i>
                                                    <i class="yellow fa fa-star"></i>
                                                    <i class="yellow fa fa-star"></i>
                                                    <i class="yellow fa fa-star"></i>
                                                    <i class="fa fa-star"></i> --}}
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
                                        @php
                                            $after_discount=($product->price-($product->price*$product->discount)/100);
                                        @endphp
                                        <h3><small><del class="text-muted">${{number_format($product->price,2)}}</del></small>    ${{number_format($after_discount,2)}}  </h3>
                                        <div class="quickview-peragraph">
                                            <p>{!! html_entity_decode($product->summary) !!}</p>
                                        </div>
                                        @if($product->size)
                                            <div class="size">
                                                <div class="row">
                                                    <div class="col-lg-6 col-12">
                                                        <h5 class="title">Size</h5>
                                                        <select>
                                                            @php
                                                            $sizes=explode(',',$product->size);
                                                            // dd($sizes);
                                                            @endphp
                                                            @foreach($sizes as $size)
                                                                <option>{{$size}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    {{-- <div class="col-lg-6 col-12">
                                                        <h5 class="title">Color</h5>
                                                        <select>
                                                            <option selected="selected">orange</option>
                                                            <option>purple</option>
                                                            <option>black</option>
                                                            <option>pink</option>
                                                        </select>
                                                    </div> --}}
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

@push('styles')
    <style>
        /* Banner Sliding */
        #Gslider .carousel-inner {
        background: #000000;
        color:black;
        }

        /*#Gslider .carousel-inner{*/
        /*height: 550px;*/
        /*}*/
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
        /*@media only screen and (max-width: 2600px){*/
        /*    #Gslider .carousel-inner{*/
        /*        height: 1050px;*/
        /*    }*/
        /*}*/
        /*@media only screen and (max-width: 1400px){*/
        /*    #Gslider .carousel-inner{*/
        /*        height: 550px;*/
        /*    }*/
        /*}*/
        
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
