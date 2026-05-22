@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || Exchange Policy')

@section('main-content')
<style>
    #content {
        font-size: 14px;
        line-height: 1.9;
        color: #555;
    }

    #content p {
        margin-bottom: 14px;
    }

    #content h5 {
        font-size: 16px;
        font-weight: 700;
        color: #222;
        margin-top: 35px;
        margin-bottom: 18px;
    }

    #content hr {
        margin: 30px 0;
        border-top: 1px solid #e5e5e5;
    }

    .tc-list {
        margin: 15px 0 20px 25px;
        padding: 0;
    }

    .tc-list li {
        position: relative;
        padding-left: 18px;
        margin-bottom: 12px;
        line-height: 1.8;
        color: #555;
    }

    .tc-list li::before {
        content: "•";
        position: absolute;
        left: 0;
        top: 0;
        color: #000;
        font-size: 18px;
        font-weight: bold;
    }

    #content a {
        color: #007bff;
        text-decoration: none;
    }

    #content a:hover {
        text-decoration: underline;
    }

    .about-content h3 {
        font-size: 42px;
        font-weight: 700;
        margin-bottom: 25px;
    }

    @media (max-width: 767px) {
        .about-content h3 {
            font-size: 30px;
        }

        #content h5 {
            font-size: 22px;
        }

        #content {
            font-size: 15px;
        }
    }
</style>
	<!-- Breadcrumbs -->
	<div class="breadcrumbs">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="bread-inner">
						<ul class="bread-list">
							<li><a href="{{ route('home') }}">Home<i class="ti-arrow-right"></i></a></li>
							<li class="active"><a href="javascript:void(0);">Exchange Policy</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Breadcrumbs -->
<section class="about-us section">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12">
                <div class="about-content">
                    <h3>Exchange Policy</h3>
                </div>
            </div>
            <div id="information-information" class="container">
                <div class="row">
                    <div id="content" class="col-sm-12">
                        <ul class="tc-list">
                            <li>We offer a <b>free exchange within 15 days of delivery.</b></li>
                            <li>To request an exchange, please click on the <a href="#">Click here to exchange (Link) option.</a></li>
                            <li>Exchange is <b>not applicable on free products.</b></li>
                            <li>Size and color exchanges are subject to <b>availability of stock</b></li>
                            <li>Only one-time exchange is allowed per order. </li>
                            <li>If you receive the wrong product, an <b>unboxing video is mandatory</b> for verification. </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@endsection
