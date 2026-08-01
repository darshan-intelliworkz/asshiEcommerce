@extends('frontend.layouts.master')

@section('main-content')
	<!-- Breadcrumbs -->
	<div class="breadcrumbs">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="bread-inner">
						<ul class="bread-list">
							<li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
							<li class="active"><a href="javascript:void(0);">Contact</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End Breadcrumbs -->
  
	<!-- Start Contact -->
	<section id="contact-us" class="contact-us section">
		<div class="container">
				<div class="contact-head">
					<div class="row">
						<div class="col-lg-8 col-12">
							<div class="form-main">
								<div class="title">
									@php
										$settings=DB::table('settings')->get();
									@endphp
									<h4>Get in touch</h4>
									<h3>Write us a message </h3>
								</div>
								<form class="form-contact form contact_form" method="post" action="{{route('contact.store')}}" id="contactForm" novalidate="novalidate">
									@csrf
									<div class="row">
										<div class="col-lg-6 col-12">
											<div class="form-group">
												<label>Your Name<span>*</span></label>
												<input name="name" id="name" type="text" placeholder="Enter your name" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '').replace(/\s{2,}/g, ' ').trimStart();">
											</div>
										</div>
										<div class="col-lg-6 col-12">
											<div class="form-group">
												<label>Your Subjects<span>*</span></label>
												<input name="subject" type="text" id="subject" placeholder="Enter Subject">
											</div>
										</div>
										<div class="col-lg-6 col-12">
											<div class="form-group">
												<label>Your Email<span>*</span></label>
												<input name="email" type="email" id="email" placeholder="Enter email address">
											</div>	
										</div>
										<div class="col-lg-6 col-12">
											<div class="form-group">
												<label>Your Phone<span>*</span></label>
												<input id="phone" name="phone" type="number" placeholder="Enter your phone" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);">
											</div>	
										</div>
										<div class="col-12">
											<div class="form-group message">
												<label>your message<span>*</span></label>
												<textarea name="message" id="message" cols="30" rows="9" placeholder="Enter Message"></textarea>
											</div>
										</div>
										<div class="col-12">
											<div class="form-group button">
												<button type="submit" class="btn ">Send Message</button>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
						<div class="col-lg-4 col-12">
							<div class="single-head">
								<div class="single-info">
									<i class="fa fa-phone"></i>
									<h4 class="title">Call us Now:</h4>
									<ul>
										<li>@foreach($settings as $data) <a href="tel:{{ preg_replace('/\s+/', '', $data->phone) }}">
                                        {{ $data->phone }}
                                    </a> @endforeach</li>
									</ul>
								</div>
								<div class="single-info">
									<i class="fa fa-envelope-open"></i>
									<h4 class="title">Email:</h4>
									<ul>
										<li><a href="mailto:info@yourwebsite.com">@foreach($settings as $data) {{$data->email}} @endforeach</a></li>
									</ul>
								</div>
								<div class="single-info">
									<i class="fa fa-location-arrow"></i>
									<h4 class="title">Ahmedabad Office:</h4>
									<ul>
										<li>@foreach($settings as $data) {{$data->address}} @endforeach</li>
									</ul>
<!--									<h4 class="title mt-3">Mumbai Office:</h4>-->
<!--									<ul>-->
<!--										<li>Pearl B/1805, Dosti Desire, Near Bramhand Phase 1-->
<!--Off. Ghodbandar Road, Thane -->
<!--(West) -400607</li>-->
<!--									</ul>-->
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
	</section>
	<!--/ End Contact -->
	
	<!--factory Address section -->
	<section class="factory-addr-wrapper">
	    <div class="container">
	        <div class="row">
	            <div class="col-xl-6 col-sm-12 mt-3">
	               <div class="factory-addr-box">
	                   <div class="map-box">
	                       <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7339.613067305974!2d72.481621!3d23.104177!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395e9dfec2f25def%3A0xb4239c72f52e4ad0!2sAASHI%20RAINWEAR!5e0!3m2!1sen!2sin!4v1726650533865!5m2!1sen!2sin" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
	                   </div>
	                   <div class="addr-box">
	                       <h3>Factory-I</h3>
	                       <p>843/2, Nidhi Industrial Estate, 
Rakanpur Village, Santej. Ta. Kalol, Gandhinagar-382721</p>
	                   </div>
	               </div>
	            </div>
	            <div class="col-xl-6 col-sm-12 mt-3">
	                <div class="factory-addr-box">
	                    <div class="map-box">
	                       <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3666.244356260624!2d72.3466647743745!3d23.234193458399638!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395c27e2e8f2d8f9%3A0xddac5bb83151418e!2sAashi%20Rainwear!5e0!3m2!1sen!2sin!4v1726651000466!5m2!1sen!2sin" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
	                   </div>
	                   <div class="addr-box">
	                       <h3>Factory-II</h3>
	                       <p>Aashi Estate, Survey No. 906, Borisana-Karsanpur Road,
Borisana, Kadi, Mehsana-384441</p>
	                   </div>
	               </div>
	            </div>
	            <div class="col-xl-6 col-sm-12 mt-3">
	                <div class="factory-addr-box">
	                    <div class="map-box">
	                       <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3740.412337557158!2d72.92532157428705!3d20.365881910217567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be0cf82e2cbf7ab%3A0xcda3830c91019d49!2sNew%20Aashi%20Rainwear!5e0!3m2!1sen!2sin!4v1726651364051!5m2!1sen!2sin" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
	                   </div>
	                   <div class="addr-box">
	                       <h3>Factory-III</h3>
	                       <p>Plot No. 160/8, Opp. Tata Motors, Near Creative Mill,
2nd Phase,<br> GIDC, Vapi-396195</p>
	                   </div>
	               </div>
	            </div>
	            <div class="col-xl-6 col-sm-12 mt-3">
	                <div class="factory-addr-box">
	                    <div class="map-box">
	                      <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7308.573477600088!2d74.0153094!3d23.6657017!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39673d007964cfff%3A0xe992fdd6e6369b5e!2sRico%20industrial%20sagwara!5e0!3m2!1sen!2sin!4v1726723882841!5m2!1sen!2sin" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
	                   </div>
	                   <div class="addr-box">
	                       <h3>Factory-IV</h3>
	                       <p>E/49/B, RIICO Industrial Estate,
Sagwara, Rajasthan-314025</p>
	                   </div>
	               </div>
	            </div>
	        
	        </div>
	    </div>
	</section>
	<!--factory Address section -->
	
	
	<!-- Map Section -->
	<div class="map-section">
		<div id="myMap">
			<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7339.613067305974!2d72.481621!3d23.104177!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395e9dfec2f25def%3A0xb4239c72f52e4ad0!2sAASHI%20RAINWEAR!5e0!3m2!1sen!2sin!4v1726657977176!5m2!1sen!2sin" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
		</div>
	</div>
	<!--/ End Map Section -->
	
	<!-- Start Shop Newsletter  -->
	{{-- @include('frontend.layouts.newsletter') --}}
	<!-- End Shop Newsletter -->
	<!--================Contact Success  =================-->
	<div class="modal fade" id="success" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
		<div class="modal-dialog" role="document">
		  <div class="modal-content">
			<div class="modal-header">
				<h2 class="text-success">Thank you!</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="text-success">Your message is successfully sent...</p>
			</div>
		  </div>
		</div>
	</div>
	
	<!-- Modals error -->
	<div class="modal fade" id="error" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
		  <div class="modal-content">
			<div class="modal-header">
				<h2 class="text-warning">Sorry!</h2>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p class="text-warning">Something went wrong.</p>
			</div>
		  </div>
		</div>
	</div>
@endsection

@push('styles')
<style>
	.modal-dialog .modal-content .modal-header{
		position:initial;
		padding: 10px 20px;
		border-bottom: 1px solid #e9ecef;
	}
	.modal-dialog .modal-content .modal-body{
		height:100px;
		padding:10px 20px;
	}
	.modal-dialog .modal-content {
		width: 50%;
		border-radius: 0;
		margin: auto;
	}
</style>
@endpush
@push('scripts')
<script src="{{ asset('public/frontend/js/jquery.form.js') }}"></script>
<script src="{{ asset('public/frontend/js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('public/frontend/js/contact.js') }}"></script>
@endpush