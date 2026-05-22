@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || Return & Refund Policy')

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
							<li class="active"><a href="javascript:void(0);">Return & Refund Policy</a></li>
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
                    <h3>Return & Refund Policy</h3>
                </div>
            </div>
            <div id="information-information" class="container">
                <div class="row">
                    <div id="content" class="col-sm-12">

                        <p>We offer a <b>7-day return policy</b> on eligible products.</p>
                    
                        <ul class="tc-list">
                            <li>Returns or exchanges are accepted only for items in their <b> original condition and packaging.</b></li>
                            <li>To initiate an exchange, please click on the <a href="#">"Click here to exchange (Link)" option.</a></li>
                        </ul>
                    
                        <hr>
                    
                        <h5>Refund Process</h5>
                    
                        <ul class="tc-list">
                            <li>Refunds will be issued to your <b>original payment method</b> once the returned product is received and verified.</li>
                            <li>For <b>Cash on Delivery (COD)</b> orders, you will be required to provide valid bank account details to process a secure refund.</li>
                            <li>Refunds are typically processed within <b>2–4 working days </b> after the returned item is received.</li>
                        </ul>
                    
                        <hr>
                    
                        <h5>Return Conditions</h5>

                        <ul class="tc-list">
                            <li>Return requests can only be made <b>after delivery of the product.</b></li>
                            <li>Return requests must be raised within <b>7 days of delivery.</b></li>
                            <li>If you receive a wrong product, an <b>unboxing video is mandatory</b> for verification.</li>
                            <li>If the product is not serviceable in your area, you will need to <b>self-ship the item using reverse logistics.</b></li>
                        </ul>
                        <hr>
                        <h5>Delivery & Safety Guidelines</h5>
                        <ul class="tc-list">
                            <li>Do not accept any package that appears <b>tampered or damaged.</b></li>
                            <li>Do not share the <b>OTP (One-Time Password)</b> with the delivery partner if you have not received the product.</li>
                        </ul>
                        <hr>
                        <h5>Cancellation Policy</h5>
                    
                        <ul class="tc-list">
                            <li>To cancel an order, please <b>contact us</b> via the “Contact Us” page.</li>
                            <li>Our team will respond within <b>24 hours.</b></li>
                        </ul>
                        <hr>
                        <h5>Agreement</h5>
                        <p>
                            By using our platform or placing an order, you agree to this Return & Refund Policy in full.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@endsection
