@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || About Us')

@section('main-content')

	<!-- Breadcrumbs -->
	<div class="breadcrumbs">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="bread-inner">
						<ul class="bread-list">
							<li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
							<li class="active"><a href="javascript:void(0);">About Us</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Breadcrumbs -->

	<!-- About Us -->
	<!--<div class="Group_Companies">-->
 <!--           <div class="container">-->
 <!--               <div class="row">-->
 <!--                   <div class="col-12">-->
 <!--                       <div class="companies-text">-->
	<!--						<h3>Welcome To <span>Aashi Group of Companies</span></h3>-->
 <!--                           <p>A group of Four Companies founded by a single individual Mr. Pradeep Trivedi. These companies-->
 <!--                               comprises of Manufacturing Factories which produce Luggage &amp; Travelling Bags, Rain wears, PVC-->
 <!--                               Lay-Flat Tubing, Windcheaters and a large quantity of PVC Packaging Bags for Home Furnishing-->
 <!--                               products.</p>-->
 <!--                       </div>-->
 <!--                   </div>-->
 <!--               </div>-->
 <!--           </div>-->
 <!--       </div>-->
	<section class="about-us section">
			<div class="container">
				<div class="row mb-5">
				      <div class="col-12">
                        <div class="about-content">
							<h3>Welcome To <span>Aashi Group of Companies</span></h3>
                            <p>A group of four companies founded by a single visionary individual, Mr. Pradeep Trivedi, engaged in diverse manufacturing sectors with a strong reputation for quality, innovation, and reliability. The group operates advanced manufacturing facilities producing premium luggage & travelling bags, rainwear products, PVC lay-flat tubing, winter wear products, and PVC packaging bags for home furnishing products, delivering excellence and customer satisfaction across various markets.</p>
                        </div>
                    </div>
                    </div>
                    <div class="row">
					<div class="col-lg-6 col-12">
						<div class="about-content">
							@php
								$settings=DB::table('settings')->get();
							@endphp
							<h3>Company Profile</h3>
							<!--<p>"AASHI PLASTIC PVT. LTD." (Since 1998), is one of the leading and reputed names in PVC packing bag industries, situated in Ahmedabad, Gujarat, India. It was initiated by Mr. Pradip Trivedi aimed to be one of the best suppliers of PVC packing Bags across the globe. From the time of our inception "AASHI PLASTIC PVT. LTD." is engaged in qualitative products and exports of different quality & style in stitching & sealing PVC Bags, PEVA Bags, PP Box, Canvas Bag, Non-Woven Bags as per the Customer Requirements & Specifications in the business of branded packaging. Each product is made with precision & perfection to meet our client requirements. The quality of our operations makes us proud and thus we are becoming a market leader through our exceptional quality products. Furthermore, we never compromise on quality aspect. Our superior quality, competitive price and on-time delivery gives us a high duke over our competitors.</p>-->


							<p>@foreach($settings as $data) {{$data->description}} @endforeach</p>
							<div class=" d-none button">
								<a href="{{route('blog')}}" class="btn">Our Blog</a>
								<a href="{{route('contact')}}" class="btn primary">Contact Us</a>
							</div>
						</div>
					</div>
					<div class="col-lg-6 col-12">
						<div class="about-img overlay">
							{{-- <div class="button">
								<a href="https://www.youtube.com/watch?v=nh2aYrGMrIE" class="video video-popup mfp-iframe"><i class="fa fa-play"></i></a>
							</div> --}}
							<img src="@foreach($settings as $data) {{asset($data->photo)}} @endforeach" alt="@foreach($settings as $data) {{asset($data->photo)}} @endforeach">
						</div>
					</div>
				</div>
					
			</div>
	</section>
	
	<section class="about-us section">
	    <div class="container">
	        <div class="row">
					<div class="col-lg-6 col-12">
					 
					<div class="about-img overlay">
							{{-- <div class="button">
								<a href="https://www.youtube.com/watch?v=nh2aYrGMrIE" class="video video-popup mfp-iframe"><i class="fa fa-play"></i></a>
							</div> --}}
							<div class="team-box">
							<div class="team-img">
                        <img src="{{ asset('public/images/Pradip-Trivedi.jpg') }}" alt="">
                        </div>
                        <div class="intro">
						    <h5>Mr. Pradip Trivedi</h5>
						    <h6>(Managing Director)</h6>
						</div>
						</div>
						</div>
						
						
					</div>
					<div class="col-lg-6 col-12">
						<div class="about-img team-img overlay">
							{{-- <div class="button">
								<a href="https://www.youtube.com/watch?v=nh2aYrGMrIE" class="video video-popup mfp-iframe"><i class="fa fa-play"></i></a>
							</div> --}}
								<div class="team-box">
								<div class="team-img">
                        <img src="{{ asset('public/images/Chhaya-Trivedi.jpg') }}" alt="">
                        </div>
                          <div class="intro">
                             <h5> Mrs. Chhaya Trivedi</h5>

                               <h6> (Director)</h6>
                              </div>
                        </div>
						</div>
					</div>
				</div>
	        </div>
	</section>
	

 <!--   <section>-->
 <!--       <div class="row">-->

	<!--		<div class="col-md-6 col-12">-->
	<!--			<div class="about-img overlay">-->
	<!--				{{-- <div class="button">-->
	<!--					<a href="https://www.youtube.com/watch?v=nh2aYrGMrIE" class="video video-popup mfp-iframe"><i class="fa fa-play"></i></a>-->
	<!--				</div> --}}-->
 <!--                       <img src="{{ asset('public/images/Pradip-Trivedi.jpg') }}" alt="">-->
	<!--			</div>-->
	<!--		</div>-->
	<!--			<div class="col-md-6 col-12">-->
	<!--			<div class="about-img overlay">-->
	<!--				{{-- <div class="button">-->
	<!--					<a href="https://www.youtube.com/watch?v=nh2aYrGMrIE" class="video video-popup mfp-iframe"><i class="fa fa-play"></i></a>-->
	<!--				</div> --}}-->
	<!--				<img src="@foreach($settings as $data) {{asset($data->photo)}} @endforeach" alt="@foreach($settings as $data) {{asset($data->photo)}} @endforeach">-->
	<!--			</div>-->
	<!--		</div>-->
	<!--		</div>-->
	    
	<!--</section>-->
	<section class="about-us section">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 col-12">
						<div class="about-content">
							<h3> <span>Our Vision</span></h3>
							<p>New Aashi Rainwear envisions becoming a globally recognized leader in innovative rainwear solutions, setting new benchmarks in quality, design, and performance. We strive to redefine protection and comfort in all weather conditions through continuous innovation and advanced manufacturing excellence.</p>
						</div>
					</div>
				</div>
				<div class="row mt-3 mb-5">
					<div class="col-lg-6 col-12">
						<div class="about-content">
						    <div class="about-img overlay">
							{{-- <div class="button">
								<a href="https://www.youtube.com/watch?v=nh2aYrGMrIE" class="video video-popup mfp-iframe"><i class="fa fa-play"></i></a>
							</div> --}}
                        <img src="{{ asset('public/images/vission-aashi.jpg') }}" alt="">
						</div>
							
						</div>
					</div>
					<div class="col-lg-6 col-12">
				
						<p>Guided by the visionary leadership of our founder, <b>Mr. Pradeep Trivedi</b>, and strengthened by our ISO 9001:2015 certification, we are committed to maintaining world-class quality standards while building long-term trust with our customers and partners. Our vision is to evolve with changing times, embrace new technologies, and deliver products that consistently exceed expectations.</p></br>
						 <p>We aim to create a future where New Aashi Rainwear stands as a symbol of reliability, innovation, and excellence in protective wear.</p>
							
					</div>
				</div>
			</div>
	</section>
	
	

	<!-- End About Us -->


	<!-- Start Shop Services Area -->
	<section class=" d-none shop-services section mb-5">
		<div class="container">
			<div class="row">
				<!--<div class="col-lg-3 col-md-6 col-12">-->
				<!--	<div class="single-service">-->
				<!--		<i class="ti-rocket"></i>-->
				<!--		<h4>Free shiping</h4>-->
				<!--		<p>Orders over $100</p>-->
				<!--	</div>-->
				<!--</div>-->
				<!--<div class="col-lg-3 col-md-6 col-12">-->
				<!--	<div class="single-service">-->
				<!--		<i class="ti-reload"></i>-->
				<!--		<h4>Free Return</h4>-->
				<!--		<p>Within 30 days returns</p>-->
				<!--	</div>-->
				<!--</div>-->
				<div class="col-lg-6 col-md-6 col-12">
					<!-- Start Single Service -->
					<div class="single-service">
						<i class="ti-lock"></i>
						<h4>Sucure Payment</h4>
						<p>100% secure payment</p>
					</div>
					<!-- End Single Service -->
				</div>
				<div class="col-lg-6 col-md-6 col-12">
					<!-- Start Single Service -->
					<div class="single-service">
						<i class="ti-tag"></i>
						<h4>Best Peice</h4>
						<p>Guaranteed price</p>
					</div>
					<!-- End Single Service -->
				</div>
			</div>
		</div>
	</section>
	<!-- End Shop Services Area -->

@endsection
