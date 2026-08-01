<!-- Meta Tag -->
@yield('meta')
<!-- Title Tag  -->
<title>{{$meta_title ?? 'Aashi - Ecommerce'}}</title>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<!--<link rel="icon" type="image/x-icon" href="">-->
<link rel="icon" href="{{ asset('public/frontend/img/aashi_retail_favicon.ico') }}" sizes="any">

<meta name="description" content="{{ $meta_description ?? 'Aashi Retail'}}"> 
<!--<meta name="robots" content="follow, index, max-snippet:-1, max-video-preview:-1, max-image-preview:large"/>-->
<meta name="robots" content="nofollow, noindex"/>
<link rel="canonical" href="{{ url()->current() }}" />

<!--OG Tags-->
<meta property="og:site_name" content="Aashi Retail">
<meta property="og:title" content="{{$meta_title ?? 'Aashi - Ecommerce'}}" />
<meta property="og:description" content="{{ $meta_description ?? 'Aashi Retail'}}" />
<meta property="og:image" content="{{ asset('public/frontend/img/logo.svg') }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">

<!--Twitter X Card Tags-->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{$meta_title ?? 'Aashi - Ecommerce'}}">
<meta name="twitter:description" content="{{ $meta_description ?? 'Aashi Retail'}}">
<meta name="twitter:image" content="{{ asset('public/frontend/img/logo.svg') }}">
    

<!-- Web Font -->
<link href="https://fonts.googleapis.com/css?family=Poppins:200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i&display=swap" rel="stylesheet">

<!-- StyleSheet -->
<link rel="manifest" href="{{ asset('public/manifest.json') }}">
<!-- Bootstrap -->
<link rel="stylesheet" href="{{asset('public/frontend/css/bootstrap.css')}}">
<!-- Magnific Popup -->
<link rel="stylesheet" href="{{asset('public/frontend/css/magnific-popup.min.css')}}">
<!-- Font Awesome -->
<link rel="stylesheet" href="{{asset('public/frontend/css/font-awesome.css')}}">
<!-- Fancybox -->
<link rel="stylesheet" href="{{asset('public/frontend/css/jquery.fancybox.min.css')}}">
<!-- Themify Icons -->
<link rel="stylesheet" href="{{asset('public/frontend/css/themify-icons.css')}}">
<!-- Nice Select CSS -->
<link rel="stylesheet" href="{{asset('public/frontend/css/niceselect.css')}}">
<!-- Animate CSS -->
<link rel="stylesheet" href="{{asset('public/frontend/css/animate.css')}}">
<!-- Flex Slider CSS -->
<link rel="stylesheet" href="{{asset('public/frontend/css/flex-slider.min.css')}}">
<!-- Owl Carousel -->
<link rel="stylesheet" href="{{asset('public/frontend/css/owl-carousel.css')}}">
<!-- Slicknav -->
<link rel="stylesheet" href="{{asset('public/frontend/css/slicknav.min.css')}}">
<!-- Jquery Ui -->
<link rel="stylesheet" href="{{asset('public/frontend/css/jquery-ui.css')}}">

<!-- Eshop StyleSheet -->
<link rel="stylesheet" href="{{asset('public/frontend/css/reset.css')}}">
<link rel="stylesheet" href="{{asset('public/frontend/css/style.css')}}">
<link rel="stylesheet" href="{{asset('public/frontend/css/responsive.css')}}">
<link rel="shortcut icon" type="image/x-icon" href="{{asset('public/frontend/img/favicon.png')}}">
<style>
    /* Multilevel dropdown */
    .dropdown-submenu {
    position: relative;
    }

    .dropdown-submenu>a:after {
    content: "\f0da";
    float: right;
    border: none;
    font-family: 'FontAwesome';
    }

    .dropdown-submenu>.dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: 0px;
    margin-left: 0px;
    }
    
    /* Chrome, Safari, Edge, Opera */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    /* Firefox */
    input[type="number"] {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    /*
</style>
@stack('styles')
