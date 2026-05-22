@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || Terms & Conditions')

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
							<li class="active"><a href="javascript:void(0);">Terms & Conditions</a></li>
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
                    <h3>Terms & Conditions</h3>
                </div>
            </div>
            <div id="information-information" class="container">
                <div class="row">
                    <div id="content" class="col-sm-12">
                        <p>
                            These Terms & Conditions ("Terms") govern the access and use of the website operated by 
                            <strong>NEW AASHI RAINWEAR</strong>, owner of the brand name and trademark <b>"AASHI"</b> and the domain 
                            <strong>aashiretails.com</strong> ("Website", "we", "us", "our").
                        </p>
                        <br>
                        <p>
                            By accessing, browsing, or purchasing from our Website, you agree to be bound by these Terms, 
                            along with any related policies referenced herein. If you do not agree, you are advised not to use 
                            our Website or services.
                        </p>
                    
                        <hr>

                        <h5>1. Use of Website</h5>
                        <p>
                            By using this Website, you confirm that you are of legal age in your jurisdiction or have 
                            parental/guardian consent.
                        </p>
                    
                        <p>You agree not to:</p>
                        <ul class="tc-list">
                            <li>Use the Website for unlawful or unauthorized purposes</li>
                            <li>Violate any applicable laws or regulations</li>
                            <li>Introduce viruses, malware, or harmful code</li>
                            <li>Attempt to gain unauthorized access to systems or data</li>
                        </ul>
                    
                        <p>Any breach may result in immediate termination of access.</p>
                    
                        <hr>
                    
                        <h5>2. General Conditions</h5>
                        <p>We reserve the right to:</p>
                    
                        <ul class="tc-list">
                            <li>Refuse service to any user at any time</li>
                            <li>Modify or discontinue any part of the Website or services without prior notice</li>
                            <li>Update these Terms periodically, with continued use implying acceptance</li>
                        </ul>
                    
                        <p>
                            You agree not to reproduce, duplicate, copy, sell, or exploit any part of the Website or Services 
                            without written permission.
                        </p>
                    
                        <hr>
                    
                        <h5>3. Products & Services</h5>
                    
                        <p>
                            NEW AASHI RAINWEAR is an online retailer of apparel and lifestyle products. Upon order placement, 
                            we process and ship products based on availability and confirmation of payment.
                        </p>
                    
                        <p>
                            We strive to ensure product accuracy; however, availability and descriptions may change without notice.
                        </p>
                    
                        <hr>
                    
                        <h5>4. Third-Party Links</h5>
                    
                        <p>
                            Our Website may contain links to third-party platforms (such as Facebook, Instagram, YouTube etc.) 
                            for informational or promotional purposes.
                        </p>
                    
                        <p>We do not:</p>
                    
                        <ul class="tc-list">
                            <li>Control third-party websites</li>
                            <li>Endorse third-party content</li>
                            <li>Accept responsibility for their policies, content, or practices</li>
                        </ul>
                    
                        <p>Users access such platforms at their own risk.</p>
                    
                        <hr>
                    
                        <h5>5. Privacy</h5>
                    
                        <p>
                            Your use of the Website is also governed by our Privacy Policy, which explains how we collect, use, 
                            and protect your data.
                        </p>
                    
                        <p>By using our services, you agree that:</p>
                    
                        <ul class="tc-list">
                            <li>Information provided is accurate and complete</li>
                            <li>You consent to data processing as per our Privacy Policy</li>
                        </ul>
                    
                        <hr>
                    
                        <h5>6. Product Representation</h5>
                    
                        <ul class="tc-list">
                            <li>Product images are for representation purposes only</li>
                            <li>Actual colors may vary due to screen/display differences</li>
                            <li>Sizes and measurements are approximate and may slightly vary</li>
                        </ul>
                    
                        <p>
                            We make reasonable efforts to ensure accuracy but do not guarantee error-free content.
                        </p>
                    
                        <hr>
                    
                        <h5>7. Pricing & Payments</h5>
                    
                        <ul class="tc-list">
                            <li>Prices are subject to change without prior notice</li>
                            <li>In case of pricing errors, we reserve the right to cancel or correct the order</li>
                            <li>If payment has been made for a cancelled order, a full refund will be issued</li>
                            <li>All prices are inclusive of applicable taxes (GST where applicable)</li>
                        </ul>
                    
                        <p>
                            Payment is charged once the order is confirmed and accepted.
                        </p>
                    
                        <p>
                            For payment support, please write to us at:
                            <a href="mailto:support@aashirainwear.com">support@aashirainwear.com</a>
                        </p>
                    
                        <hr>
                    
                        <!--<h5>8. Returns, Exchanges & Refunds</h5>-->
                    
                        <!--<p>We offer the following policies:</p>-->
                    
                        <!--<ul class="tc-list">-->
                        <!--    <li>We offer only a 7-day return policy for eligible products.</li>-->
                        <!--    <li>You can request an exchange within 15 days from the date of delivery.</li>-->
                        <!--    <li>Please note only one exchange request is allowed for each order.</li>-->
                        <!--    <li>Refunds will be processed to the original payment method upon receipt of the returned product.</li>-->
                        <!--    <li>Refunds will be initiated within 2–4 working days after we receive the returned product.</li>-->
                        <!--    <li>If the payment was made through COD, you will be prompted to provide your bank account details for a secure refund transaction.</li>-->
                        <!--    <li>If you have received an incorrect product, please share an unboxing video of the package for verification purposes.</li>-->
                        <!--    <li>Do not accept tampered packages.</li>-->
                        <!--    <li>Cancellation requests must be made via Contact Us section.</li>-->
                        <!--</ul>-->
                    
                        <!--<p>We aim to respond within 24 hours.</p>-->
                    
                        <!--<p>-->
                        <!--    For more information about our Return & Exchange Policy,-->
                        <!--    <a href="#">click here</a>.-->
                        <!--</p>-->
                    
                        <!--<p>-->
                        <!--    To place a Return or Exchange request,-->
                        <!--    <a href="#">click here</a>.-->
                        <!--</p>-->
                    
                        <!--<hr>-->
                    
                        <h5>8. Product Maintenance & Care</h5>
                    
                        <p>
                            To maintain the quality of the products purchased, we recommend the following care instructions:
                        </p>
                    
                        <ul class="tc-list">
                            <li>Clean the product using running water for minor cleaning</li>
                            <li>Do not iron the product</li>
                            <li>Do not tumble dry the product</li>
                        </ul>
                    
                        <hr>
                    
                        <h5>9. User Content & Reviews</h5>
                    
                        <ul class="tc-list">
                            <li>
                                In case a product is out of stock, you may contact NEW AASHI RAINWEAR using the contact details provided to get notified when the product is restocked.
                            </li>
                    
                            <li>
                                Any review, feedback, comment, submission, or similar content posted by you on the Site may be displayed publicly at the sole discretion of NEW AASHI RAINWEAR.
                            </li>
                    
                            <li>
                                Such content will not be altered, modified, or moderated by NEW AASHI RAINWEAR.
                            </li>
                    
                            <li>
                                By submitting content, you agree that NEW AASHI RAINWEAR may use your comments, ideas, or suggestions without prior notice, compensation, or acknowledgment for any purpose.
                            </li>
                    
                            <li>
                                NEW AASHI RAINWEAR does not review all user-generated content posted on the Site and is not responsible for its accuracy or nature.
                            </li>
                    
                            <li>
                                The Site acts only as a platform to display user content and does not take responsibility or liability for user-generated posts or activities.
                            </li>
                    
                            <li>
                                You must not post or upload any content that is abusive, harassing, threatening, defamatory, obscene, fraudulent, misleading, or violates any legal or intellectual property rights.
                            </li>
                    
                            <li>
                                You are not allowed to upload commercial content or use the Site to promote or solicit other commercial services or organizations.
                            </li>
                        </ul>
                    
                        <hr>
                    
                        <h5>10. Intellectual Property Rights</h5>
                    
                        <p>
                            All content, branding, logos, product designs, and materials are the exclusive property of 
                            NEW AASHI RAINWEAR and are protected under applicable intellectual property laws.
                        </p>
                    
                        <p>Unauthorized use is strictly prohibited.</p>
                    
                        <hr>
                    
                        <h5>11. Limitation of Liability</h5>
                    
                        <p>We shall not be liable for:</p>
                    
                        <ul class="tc-list">
                            <li>Indirect, incidental, or consequential damages</li>
                            <li>Losses arising from Website use or inability to use services</li>
                            <li>Delays or disruptions beyond our control</li>
                        </ul>
                    
                        <hr>
                    
                        <h5>12. Indemnification</h5>
                    
                        <p>
                            You agree to indemnify and hold harmless NEW AASHI RAINWEAR, its directors, employees, and affiliates from any claims, damages, or liabilities arising from:
                        </p>
                    
                        <ul class="tc-list">
                            <li>Your use of the Website</li>
                            <li>Violation of these Terms</li>
                            <li>Infringement of any rights of third parties</li>
                        </ul>
                    
                        <hr>
                    
                        <h5>13. Termination</h5>
                    
                        <p>
                            We reserve the right to suspend or terminate access to the Website without prior notice in case of violation of these Terms.
                        </p>
                    
                        <p>Any pending payments for orders already placed will remain payable.</p>
                    
                        <hr>
                    
                        <h5>14. Governing Law & Jurisdiction</h5>
                    
                        <p>
                            These Terms are governed by the laws of India. All disputes shall be subject to the exclusive jurisdiction of competent courts in India.
                        </p>
                    
                        <hr>
                    
                        <h5>15. Contact Information</h5>
                    
                        <p>
                            For any questions or concerns regarding these Terms:
                        </p>
                    
                        <p>
                            Email:
                            <a href="mailto:support@aashirainwear.com">
                                support@aashirainwear.com
                            </a>
                        </p>
                    
                        <p>
                            Contact Number:
                            <a href="tel:+918511985585">
                                +91 8511985585
                            </a>
                        </p>
                    
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@endsection
