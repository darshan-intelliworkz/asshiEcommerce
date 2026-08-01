@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || Checkout page')

@section('main-content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div id="checkout-loader-overlay" style="display:none; position:fixed; inset:0; background:rgba(255,255,255,0.9); z-index:99999; align-items:center; justify-content:center; flex-direction:column;">
        <div style="width:54px;height:54px;border:6px solid #f3f3f3;border-top-color:#f7941d;border-radius:50%;animation:spin 1s linear infinite;"></div>
        <p style="margin-top:15px;font-size:16px;font-weight:600;color:#333; text-align:center; max-width:320px;">Placing your order. Please wait. Do not press anything until payment is done.</p>
    </div>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0)">Checkout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->
            
    <!-- Start Checkout -->
    <section class="shop checkout section">
        <div class="container">
                <form id="checkout-form" class="form" method="POST" action="{{route('cart.order')}}">
                    @csrf
                    <div class="row"> 

                        <div class="col-lg-8 col-12">
                            <div class="checkout-form">
                                <h2>Make Your Checkout Here</h2>
                                {{-- <p>Please register in order to checkout more quickly</p> --}}

                                <div class="row mb-3 mt-3">
                                    <div class="col-lg-12 col-md-12 col-12">
                                        <div class="single-widget">
                                            <label><b>Select Payment Type</b></label>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label><input name="payment_method" type="radio" value="cod" onchange="checkshipingcharges(this)" {{ old('payment_method') == 'cod' ? 'checked' : '' }}>  Cash On Delivery</label>
                                                    </div>
                                                    <div class="col-md-4">
                                                    {{-- <input name="payment_method"  type="radio" value="paypal" onchange="checkshipingcharges(this)"> <label> PayPal</label>  --}}
                                                    <label><input name="payment_method" type="radio" value="razorpay" onchange="checkshipingcharges(this)" {{ old('payment_method') == 'razorpay' ? 'checked' : '' }}/> Online Payment</label>
                                                    </div>
                                                </div>
                                        </div>
                                        {{-- <div class="single-widget payement">
                                            <div class="content">
                                                <img src="{{('public/backend/img/payment-method.png')}}" alt="#">
                                            </div>
                                        </div> --}}
                                    </div>
                                    @error('payment_method')
                                        <span class='text-danger'>{{$message}}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>First Name<span>*</span></label>
                                            <input type="text" name="first_name" placeholder="" value="{{old('first_name')}}" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '').replace(/\s+/g, ' ').trimStart();">
                                            @error('first_name')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Last Name<span>*</span></label>
                                            <input type="text" name="last_name" placeholder="" value="{{old('last_name')}}" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '').replace(/\s+/g, ' ').trimStart();">
                                            @error('last_name')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Email Address<span>*</span></label>
                                            <input type="email" name="email" placeholder="" value="{{old('email')}}">
                                            @error('email')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Phone Number <span>*</span></label>
                                            <input type="tel" name="phone" maxlength="10" inputmode="numeric" pattern="[0-9]{10}" placeholder="" value="{{old('phone')}}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);">
                                            @error('phone')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Postal Code<span>*</span></label>
                                            <input type="text" name="post_code" id="post_code" placeholder="" value="{{old('post_code')}}" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 15);">
                                            @error('post_code')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                            <span id="delivery_status"></span>
                                            <span id="cod_status"></span>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Country</label>
                                            <input type="text" name="country" placeholder="India" value="India" readonly>
                                            {{-- <select name="country" id="country">
                                                <option value="IN" selected readonly>India</option>
                                            </select> --}}
                                            {{-- <select name="country" id="country">
                                                <option value="0">Select Country</option>
                                                <option value="AF">Afghanistan</option>
                                                <option value="AX">Åland Islands</option>
                                                <option value="AL">Albania</option>
                                                <option value="DZ">Algeria</option>
                                                <option value="AS">American Samoa</option>
                                                <option value="AD">Andorra</option>
                                                <option value="AO">Angola</option>
                                                <option value="AI">Anguilla</option>
                                                <option value="AQ">Antarctica</option>
                                                <option value="AG">Antigua and Barbuda</option>
                                                <option value="AR">Argentina</option>
                                                <option value="AM">Armenia</option>
                                                <option value="AW">Aruba</option>
                                                <option value="AU">Australia</option>
                                                <option value="AT">Austria</option>
                                                <option value="AZ">Azerbaijan</option>
                                                <option value="BS">Bahamas</option>
                                                <option value="BH">Bahrain</option>
                                                <option value="BD">Bangladesh</option>
                                                <option value="BB">Barbados</option>
                                                <option value="BY">Belarus</option>
                                                <option value="BE">Belgium</option>
                                                <option value="BZ">Belize</option>
                                                <option value="BJ">Benin</option>
                                                <option value="BM">Bermuda</option>
                                                <option value="BT">Bhutan</option>
                                                <option value="BO">Bolivia</option>
                                                <option value="BA">Bosnia and Herzegovina</option>
                                                <option value="BW">Botswana</option>
                                                <option value="BV">Bouvet Island</option>
                                                <option value="BR">Brazil</option>
                                                <option value="IO">British Indian Ocean Territory</option>
                                                <option value="VG">British Virgin Islands</option>
                                                <option value="BN">Brunei</option>
                                                <option value="BG">Bulgaria</option>
                                                <option value="BF">Burkina Faso</option>
                                                <option value="BI">Burundi</option>
                                                <option value="KH">Cambodia</option>
                                                <option value="CM">Cameroon</option>
                                                <option value="CA">Canada</option>
                                                <option value="CV">Cape Verde</option>
                                                <option value="KY">Cayman Islands</option>
                                                <option value="CF">Central African Republic</option>
                                                <option value="TD">Chad</option>
                                                <option value="CL">Chile</option>
                                                <option value="CN">China</option>
                                                <option value="CX">Christmas Island</option>
                                                <option value="CC">Cocos [Keeling] Islands</option>
                                                <option value="CO">Colombia</option>
                                                <option value="KM">Comoros</option>
                                                <option value="CG">Congo - Brazzaville</option>
                                                <option value="CD">Congo - Kinshasa</option>
                                                <option value="CK">Cook Islands</option>
                                                <option value="CR">Costa Rica</option>
                                                <option value="CI">Côte d’Ivoire</option>
                                                <option value="HR">Croatia</option>
                                                <option value="CU">Cuba</option>
                                                <option value="CY">Cyprus</option>
                                                <option value="CZ">Czech Republic</option>
                                                <option value="DK">Denmark</option>
                                                <option value="DJ">Djibouti</option>
                                                <option value="DM">Dominica</option>
                                                <option value="DO">Dominican Republic</option>
                                                <option value="EC">Ecuador</option>
                                                <option value="EG">Egypt</option>
                                                <option value="SV">El Salvador</option>
                                                <option value="GQ">Equatorial Guinea</option>
                                                <option value="ER">Eritrea</option>
                                                <option value="EE">Estonia</option>
                                                <option value="ET">Ethiopia</option>
                                                <option value="FK">Falkland Islands</option>
                                                <option value="FO">Faroe Islands</option>
                                                <option value="FJ">Fiji</option>
                                                <option value="FI">Finland</option>
                                                <option value="FR">France</option>
                                                <option value="GF">French Guiana</option>
                                                <option value="PF">French Polynesia</option>
                                                <option value="TF">French Southern Territories</option>
                                                <option value="GA">Gabon</option>
                                                <option value="GM">Gambia</option>
                                                <option value="GE">Georgia</option>
                                                <option value="DE">Germany</option>
                                                <option value="GH">Ghana</option>
                                                <option value="GI">Gibraltar</option>
                                                <option value="GR">Greece</option>
                                                <option value="GL">Greenland</option>
                                                <option value="GD">Grenada</option>
                                                <option value="GP">Guadeloupe</option>
                                                <option value="GU">Guam</option>
                                                <option value="GT">Guatemala</option>
                                                <option value="GG">Guernsey</option>
                                                <option value="GN">Guinea</option>
                                                <option value="GW">Guinea-Bissau</option>
                                                <option value="GY">Guyana</option>
                                                <option value="HT">Haiti</option>
                                                <option value="HM">Heard Island and McDonald Islands</option>
                                                <option value="HN">Honduras</option>
                                                <option value="HK">Hong Kong SAR China</option>
                                                <option value="HU">Hungary</option>
                                                <option value="IS">Iceland</option>
                                                <option value="IN">India</option>
                                                <option value="ID">Indonesia</option>
                                                <option value="IR">Iran</option>
                                                <option value="IQ">Iraq</option>
                                                <option value="IE">Ireland</option>
                                                <option value="IM">Isle of Man</option>
                                                <option value="IL">Israel</option>
                                                <option value="IT">Italy</option>
                                                <option value="JM">Jamaica</option>
                                                <option value="JP">Japan</option>
                                                <option value="JE">Jersey</option>
                                                <option value="JO">Jordan</option>
                                                <option value="KZ">Kazakhstan</option>
                                                <option value="KE">Kenya</option>
                                                <option value="KI">Kiribati</option>
                                                <option value="KW">Kuwait</option>
                                                <option value="KG">Kyrgyzstan</option>
                                                <option value="LA">Laos</option>
                                                <option value="LV">Latvia</option>
                                                <option value="LB">Lebanon</option>
                                                <option value="LS">Lesotho</option>
                                                <option value="LR">Liberia</option>
                                                <option value="LY">Libya</option>
                                                <option value="LI">Liechtenstein</option>
                                                <option value="LT">Lithuania</option>
                                                <option value="LU">Luxembourg</option>
                                                <option value="MO">Macau SAR China</option>
                                                <option value="MK">Macedonia</option>
                                                <option value="MG">Madagascar</option>
                                                <option value="MW">Malawi</option>
                                                <option value="MY">Malaysia</option>
                                                <option value="MV">Maldives</option>
                                                <option value="ML">Mali</option>
                                                <option value="MT">Malta</option>
                                                <option value="MH">Marshall Islands</option>
                                                <option value="MQ">Martinique</option>
                                                <option value="MR">Mauritania</option>
                                                <option value="MU">Mauritius</option>
                                                <option value="YT">Mayotte</option>
                                                <option value="MX">Mexico</option>
                                                <option value="FM">Micronesia</option>
                                                <option value="MD">Moldova</option>
                                                <option value="MC">Monaco</option>
                                                <option value="MN">Mongolia</option>
                                                <option value="ME">Montenegro</option>
                                                <option value="MS">Montserrat</option>
                                                <option value="MA">Morocco</option>
                                                <option value="MZ">Mozambique</option>
                                                <option value="MM">Myanmar [Burma]</option>
                                                <option value="NA">Namibia</option>
                                                <option value="NR">Nauru</option>
                                                <option value="NP">Nepal</option>
                                                <option value="NL">Netherlands</option>
                                                <option value="AN">Netherlands Antilles</option>
                                                <option value="NC">New Caledonia</option>
                                                <option value="NZ">New Zealand</option>
                                                <option value="NI">Nicaragua</option>
                                                <option value="NE">Niger</option>
                                                <option value="NG">Nigeria</option>
                                                <option value="NU">Niue</option>
                                                <option value="NF">Norfolk Island</option>
                                                <option value="MP">Northern Mariana Islands</option>
                                                <option value="KP">North Korea</option>
                                                <option value="NO">Norway</option>
                                                <option value="OM">Oman</option>
                                                <option value="PK">Pakistan</option>
                                                <option value="PW">Palau</option>
                                                <option value="PS">Palestinian Territories</option>
                                                <option value="PA">Panama</option>
                                                <option value="PG">Papua New Guinea</option>
                                                <option value="PY">Paraguay</option>
                                                <option value="PE">Peru</option>
                                                <option value="PH">Philippines</option>
                                                <option value="PN">Pitcairn Islands</option>
                                                <option value="PL">Poland</option>
                                                <option value="PT">Portugal</option>
                                                <option value="PR">Puerto Rico</option>
                                                <option value="QA">Qatar</option>
                                                <option value="RE">Réunion</option>
                                                <option value="RO">Romania</option>
                                                <option value="RU">Russia</option>
                                                <option value="RW">Rwanda</option>
                                                <option value="BL">Saint Barthélemy</option>
                                                <option value="SH">Saint Helena</option>
                                                <option value="KN">Saint Kitts and Nevis</option>
                                                <option value="LC">Saint Lucia</option>
                                                <option value="MF">Saint Martin</option>
                                                <option value="PM">Saint Pierre and Miquelon</option>
                                                <option value="VC">Saint Vincent and the Grenadines</option>
                                                <option value="WS">Samoa</option>
                                                <option value="SM">San Marino</option>
                                                <option value="ST">São Tomé and Príncipe</option>
                                                <option value="SA">Saudi Arabia</option>
                                                <option value="SN">Senegal</option>
                                                <option value="RS">Serbia</option>
                                                <option value="SC">Seychelles</option>
                                                <option value="SL">Sierra Leone</option>
                                                <option value="SG">Singapore</option>
                                                <option value="SK">Slovakia</option>
                                                <option value="SI">Slovenia</option>
                                                <option value="SB">Solomon Islands</option>
                                                <option value="SO">Somalia</option>
                                                <option value="ZA">South Africa</option>
                                                <option value="GS">South Georgia</option>
                                                <option value="KR">South Korea</option>
                                                <option value="ES">Spain</option>
                                                <option value="LK">Sri Lanka</option>
                                                <option value="SD">Sudan</option>
                                                <option value="SR">Suriname</option>
                                                <option value="SJ">Svalbard and Jan Mayen</option>
                                                <option value="SZ">Swaziland</option>
                                                <option value="SE">Sweden</option>
                                                <option value="CH">Switzerland</option>
                                                <option value="SY">Syria</option>
                                                <option value="TW">Taiwan</option>
                                                <option value="TJ">Tajikistan</option>
                                                <option value="TZ">Tanzania</option>
                                                <option value="TH">Thailand</option>
                                                <option value="TL">Timor-Leste</option>
                                                <option value="TG">Togo</option>
                                                <option value="TK">Tokelau</option>
                                                <option value="TO">Tonga</option>
                                                <option value="TT">Trinidad and Tobago</option>
                                                <option value="TN">Tunisia</option>
                                                <option value="TR">Turkey</option>
                                                <option value="TM">Turkmenistan</option>
                                                <option value="TC">Turks and Caicos Islands</option>
                                                <option value="TV">Tuvalu</option>
                                                <option value="UG">Uganda</option>
                                                <option value="UA">Ukraine</option>
                                                <option value="AE">United Arab Emirates</option>
                                                <option value="Uk">United Kingdom</option>
                                                <option value="UY">Uruguay</option>
                                                <option value="UM">U.S. Minor Outlying Islands</option>
                                                <option value="VI">U.S. Virgin Islands</option>
                                                <option value="UZ">Uzbekistan</option>
                                                <option value="VU">Vanuatu</option>
                                                <option value="VA">Vatican City</option>
                                                <option value="VE">Venezuela</option>
                                                <option value="VN">Vietnam</option>
                                                <option value="WF">Wallis and Futuna</option>
                                                <option value="EH">Western Sahara</option>
                                                <option value="YE">Yemen</option>
                                                <option value="ZM">Zambia</option>
                                                <option value="ZW">Zimbabwe</option>
                                            </select>
                                            <select name="country" id="country">
                                                <option value="0" {{ old('country') == '0' ? 'selected' : '' }}>Select Country</option>
                                                <option value="AF" {{ old('country') == 'AF' ? 'selected' : '' }}>Afghanistan</option>
                                                <option value="AX" {{ old('country') == 'AX' ? 'selected' : '' }}>Åland Islands</option>
                                                <option value="AL" {{ old('country') == 'AL' ? 'selected' : '' }}>Albania</option>
                                                <option value="DZ" {{ old('country') == 'DZ' ? 'selected' : '' }}>Algeria</option>
                                                <option value="AS" {{ old('country') == 'AS' ? 'selected' : '' }}>American Samoa</option>
                                                <option value="AD" {{ old('country') == 'AD' ? 'selected' : '' }}>Andorra</option>
                                                <option value="AO" {{ old('country') == 'AO' ? 'selected' : '' }}>Angola</option>
                                                <option value="AI" {{ old('country') == 'AI' ? 'selected' : '' }}>Anguilla</option>
                                                <option value="AQ" {{ old('country') == 'AQ' ? 'selected' : '' }}>Antarctica</option>
                                                <option value="AG" {{ old('country') == 'AG' ? 'selected' : '' }}>Antigua and Barbuda</option>
                                                <option value="AR" {{ old('country') == 'AR' ? 'selected' : '' }}>Argentina</option>
                                                <option value="AM" {{ old('country') == 'AM' ? 'selected' : '' }}>Armenia</option>
                                                <option value="AW" {{ old('country') == 'AW' ? 'selected' : '' }}>Aruba</option>
                                                <option value="AU" {{ old('country') == 'AU' ? 'selected' : '' }}>Australia</option>
                                                <option value="AT" {{ old('country') == 'AT' ? 'selected' : '' }}>Austria</option>
                                                <option value="AZ" {{ old('country') == 'AZ' ? 'selected' : '' }}>Azerbaijan</option>
                                                <option value="BS" {{ old('country') == 'BS' ? 'selected' : '' }}>Bahamas</option>
                                                <option value="BH" {{ old('country') == 'BH' ? 'selected' : '' }}>Bahrain</option>
                                                <option value="BD" {{ old('country') == 'BD' ? 'selected' : '' }}>Bangladesh</option>
                                                <option value="BB" {{ old('country') == 'BB' ? 'selected' : '' }}>Barbados</option>
                                                <option value="BY" {{ old('country') == 'BY' ? 'selected' : '' }}>Belarus</option>
                                                <option value="BE" {{ old('country') == 'BE' ? 'selected' : '' }}>Belgium</option>
                                                <option value="BZ" {{ old('country') == 'BZ' ? 'selected' : '' }}>Belize</option>
                                                <option value="BJ" {{ old('country') == 'BJ' ? 'selected' : '' }}>Benin</option>
                                                <option value="BM" {{ old('country') == 'BM' ? 'selected' : '' }}>Bermuda</option>
                                                <option value="BT" {{ old('country') == 'BT' ? 'selected' : '' }}>Bhutan</option>
                                                <option value="BO" {{ old('country') == 'BO' ? 'selected' : '' }}>Bolivia</option>
                                                <option value="BA" {{ old('country') == 'BA' ? 'selected' : '' }}>Bosnia and Herzegovina</option>
                                                <option value="BW" {{ old('country') == 'BW' ? 'selected' : '' }}>Botswana</option>
                                                <option value="BV" {{ old('country') == 'BV' ? 'selected' : '' }}>Bouvet Island</option>
                                                <option value="BR" {{ old('country') == 'BR' ? 'selected' : '' }}>Brazil</option>
                                                <option value="IO" {{ old('country') == 'IO' ? 'selected' : '' }}>British Indian Ocean Territory</option>
                                                <option value="VG" {{ old('country') == 'VG' ? 'selected' : '' }}>British Virgin Islands</option>
                                                <option value="BN" {{ old('country') == 'BN' ? 'selected' : '' }}>Brunei</option>
                                                <option value="BG" {{ old('country') == 'BG' ? 'selected' : '' }}>Bulgaria</option>
                                                <option value="BF" {{ old('country') == 'BF' ? 'selected' : '' }}>Burkina Faso</option>
                                                <option value="BI" {{ old('country') == 'BI' ? 'selected' : '' }}>Burundi</option>
                                                <option value="KH" {{ old('country') == 'KH' ? 'selected' : '' }}>Cambodia</option>
                                                <option value="CM" {{ old('country') == 'CM' ? 'selected' : '' }}>Cameroon</option>
                                                <option value="CA" {{ old('country') == 'CA' ? 'selected' : '' }}>Canada</option>
                                                <option value="CV" {{ old('country') == 'CV' ? 'selected' : '' }}>Cape Verde</option>
                                                <option value="KY" {{ old('country') == 'KY' ? 'selected' : '' }}>Cayman Islands</option>
                                                <option value="CF" {{ old('country') == 'CF' ? 'selected' : '' }}>Central African Republic</option>
                                                <option value="TD" {{ old('country') == 'TD' ? 'selected' : '' }}>Chad</option>
                                                <option value="CL" {{ old('country') == 'CL' ? 'selected' : '' }}>Chile</option>
                                                <option value="CN" {{ old('country') == 'CN' ? 'selected' : '' }}>China</option>
                                                <option value="CX" {{ old('country') == 'CX' ? 'selected' : '' }}>Christmas Island</option>
                                                <option value="CC" {{ old('country') == 'CC' ? 'selected' : '' }}>Cocos [Keeling] Islands</option>
                                                <option value="CO" {{ old('country') == 'CO' ? 'selected' : '' }}>Colombia</option>
                                                <option value="KM" {{ old('country') == 'KM' ? 'selected' : '' }}>Comoros</option>
                                                <option value="CG" {{ old('country') == 'CG' ? 'selected' : '' }}>Congo - Brazzaville</option>
                                                <option value="CD" {{ old('country') == 'CD' ? 'selected' : '' }}>Congo - Kinshasa</option>
                                                <option value="CK" {{ old('country') == 'CK' ? 'selected' : '' }}>Cook Islands</option>
                                                <option value="CR" {{ old('country') == 'CR' ? 'selected' : '' }}>Costa Rica</option>
                                                <option value="CI" {{ old('country') == 'CI' ? 'selected' : '' }}>Côte d’Ivoire</option>
                                                <option value="HR" {{ old('country') == 'HR' ? 'selected' : '' }}>Croatia</option>
                                                <option value="CU" {{ old('country') == 'CU' ? 'selected' : '' }}>Cuba</option>
                                                <option value="CY" {{ old('country') == 'CY' ? 'selected' : '' }}>Cyprus</option>
                                                <option value="CZ" {{ old('country') == 'CZ' ? 'selected' : '' }}>Czech Republic</option>
                                                <option value="DK" {{ old('country') == 'DK' ? 'selected' : '' }}>Denmark</option>
                                                <option value="DJ" {{ old('country') == 'DJ' ? 'selected' : '' }}>Djibouti</option>
                                                <option value="DM" {{ old('country') == 'DM' ? 'selected' : '' }}>Dominica</option>
                                                <option value="DO" {{ old('country') == 'DO' ? 'selected' : '' }}>Dominican Republic</option>
                                                <option value="EC" {{ old('country') == 'EC' ? 'selected' : '' }}>Ecuador</option>
                                                <option value="EG" {{ old('country') == 'EG' ? 'selected' : '' }}>Egypt</option>
                                                <option value="SV" {{ old('country') == 'SV' ? 'selected' : '' }}>El Salvador</option>
                                                <option value="GQ" {{ old('country') == 'GQ' ? 'selected' : '' }}>Equatorial Guinea</option>
                                                <option value="ER" {{ old('country') == 'ER' ? 'selected' : '' }}>Eritrea</option>
                                                <option value="EE" {{ old('country') == 'EE' ? 'selected' : '' }}>Estonia</option>
                                                <option value="ET" {{ old('country') == 'ET' ? 'selected' : '' }}>Ethiopia</option>
                                                <option value="FK" {{ old('country') == 'FK' ? 'selected' : '' }}>Falkland Islands</option>
                                                <option value="FO" {{ old('country') == 'FO' ? 'selected' : '' }}>Faroe Islands</option>
                                                <option value="FJ" {{ old('country') == 'FJ' ? 'selected' : '' }}>Fiji</option>
                                                <option value="FI" {{ old('country') == 'FI' ? 'selected' : '' }}>Finland</option>
                                                <option value="FR" {{ old('country') == 'FR' ? 'selected' : '' }}>France</option>
                                                <option value="GF" {{ old('country') == 'GF' ? 'selected' : '' }}>French Guiana</option>
                                                <option value="PF" {{ old('country') == 'PF' ? 'selected' : '' }}>French Polynesia</option>
                                                <option value="TF" {{ old('country') == 'TF' ? 'selected' : '' }}>French Southern Territories</option>
                                                <option value="GA" {{ old('country') == 'GA' ? 'selected' : '' }}>Gabon</option>
                                                <option value="GM" {{ old('country') == 'GM' ? 'selected' : '' }}>Gambia</option>
                                                <option value="GE" {{ old('country') == 'GE' ? 'selected' : '' }}>Georgia</option>
                                                <option value="DE" {{ old('country') == 'DE' ? 'selected' : '' }}>Germany</option>
                                                <option value="GH" {{ old('country') == 'GH' ? 'selected' : '' }}>Ghana</option>
                                                <option value="GI" {{ old('country') == 'GI' ? 'selected' : '' }}>Gibraltar</option>
                                                <option value="GR" {{ old('country') == 'GR' ? 'selected' : '' }}>Greece</option>
                                                <option value="GL" {{ old('country') == 'GL' ? 'selected' : '' }}>Greenland</option>
                                                <option value="GD" {{ old('country') == 'GD' ? 'selected' : '' }}>Grenada</option>
                                                <option value="GP" {{ old('country') == 'GP' ? 'selected' : '' }}>Guadeloupe</option>
                                                <option value="GU" {{ old('country') == 'GU' ? 'selected' : '' }}>Guam</option>
                                                <option value="GT" {{ old('country') == 'GT' ? 'selected' : '' }}>Guatemala</option>
                                                <option value="GG" {{ old('country') == 'GG' ? 'selected' : '' }}>Guernsey</option>
                                                <option value="GN" {{ old('country') == 'GN' ? 'selected' : '' }}>Guinea</option>
                                                <option value="GW" {{ old('country') == 'GW' ? 'selected' : '' }}>Guinea-Bissau</option>
                                                <option value="GY" {{ old('country') == 'GY' ? 'selected' : '' }}>Guyana</option>
                                                <option value="HT" {{ old('country') == 'HT' ? 'selected' : '' }}>Haiti</option>
                                                <option value="HM" {{ old('country') == 'HM' ? 'selected' : '' }}>Heard Island and McDonald Islands</option>
                                                <option value="HN" {{ old('country') == 'HN' ? 'selected' : '' }}>Honduras</option>
                                                <option value="HK" {{ old('country') == 'HK' ? 'selected' : '' }}>Hong Kong SAR China</option>
                                                <option value="HU" {{ old('country') == 'HU' ? 'selected' : '' }}>Hungary</option>
                                                <option value="IS" {{ old('country') == 'IS' ? 'selected' : '' }}>Iceland</option>
                                                <option value="IN" {{ old('country') == 'IN' ? 'selected' : '' }}>India</option>
                                                <option value="ID" {{ old('country') == 'ID' ? 'selected' : '' }}>Indonesia</option>
                                                <option value="IR" {{ old('country') == 'IR' ? 'selected' : '' }}>Iran</option>
                                                <option value="IQ" {{ old('country') == 'IQ' ? 'selected' : '' }}>Iraq</option>
                                                <option value="IE" {{ old('country') == 'IE' ? 'selected' : '' }}>Ireland</option>
                                                <option value="IM" {{ old('country') == 'IM' ? 'selected' : '' }}>Isle of Man</option>
                                                <option value="IL" {{ old('country') == 'IL' ? 'selected' : '' }}>Israel</option>
                                                <option value="IT" {{ old('country') == 'IT' ? 'selected' : '' }}>Italy</option>
                                                <option value="JM" {{ old('country') == 'JM' ? 'selected' : '' }}>Jamaica</option>
                                                <option value="JP" {{ old('country') == 'JP' ? 'selected' : '' }}>Japan</option>
                                                <option value="JE" {{ old('country') == 'JE' ? 'selected' : '' }}>Jersey</option>
                                                <option value="JO" {{ old('country') == 'JO' ? 'selected' : '' }}>Jordan</option>
                                                <option value="KZ" {{ old('country') == 'KZ' ? 'selected' : '' }}>Kazakhstan</option>
                                                <option value="KE" {{ old('country') == 'KE' ? 'selected' : '' }}>Kenya</option>
                                                <option value="KI" {{ old('country') == 'KI' ? 'selected' : '' }}>Kiribati</option>
                                                <option value="KW" {{ old('country') == 'KW' ? 'selected' : '' }}>Kuwait</option>
                                                <option value="KG" {{ old('country') == 'KG' ? 'selected' : '' }}>Kyrgyzstan</option>
                                                <option value="LA" {{ old('country') == 'LA' ? 'selected' : '' }}>Laos</option>
                                                <option value="LV" {{ old('country') == 'LV' ? 'selected' : '' }}>Latvia</option>
                                                <option value="LB" {{ old('country') == 'LB' ? 'selected' : '' }}>Lebanon</option>
                                                <option value="LS" {{ old('country') == 'LS' ? 'selected' : '' }}>Lesotho</option>
                                                <option value="LR" {{ old('country') == 'LR' ? 'selected' : '' }}>Liberia</option>
                                                <option value="LY" {{ old('country') == 'LY' ? 'selected' : '' }}>Libya</option>
                                                <option value="LI" {{ old('country') == 'LI' ? 'selected' : '' }}>Liechtenstein</option>
                                                <option value="LT" {{ old('country') == 'LT' ? 'selected' : '' }}>Lithuania</option>
                                                <option value="LU" {{ old('country') == 'LU' ? 'selected' : '' }}>Luxembourg</option>
                                                <option value="MO" {{ old('country') == 'MO' ? 'selected' : '' }}>Macau SAR China</option>
                                                <option value="MK" {{ old('country') == 'MK' ? 'selected' : '' }}>Macedonia</option>
                                                <option value="MG" {{ old('country') == 'MG' ? 'selected' : '' }}>Madagascar</option>
                                                <option value="MW" {{ old('country') == 'MW' ? 'selected' : '' }}>Malawi</option>
                                                <option value="MY" {{ old('country') == 'MY' ? 'selected' : '' }}>Malaysia</option>
                                                <option value="MV" {{ old('country') == 'MV' ? 'selected' : '' }}>Maldives</option>
                                                <option value="ML" {{ old('country') == 'ML' ? 'selected' : '' }}>Mali</option>
                                                <option value="MT" {{ old('country') == 'MT' ? 'selected' : '' }}>Malta</option>
                                                <option value="MH" {{ old('country') == 'MH' ? 'selected' : '' }}>Marshall Islands</option>
                                                <option value="MQ" {{ old('country') == 'MQ' ? 'selected' : '' }}>Martinique</option>
                                                <option value="MR" {{ old('country') == 'MR' ? 'selected' : '' }}>Mauritania</option>
                                                <option value="MU" {{ old('country') == 'MU' ? 'selected' : '' }}>Mauritius</option>
                                                <option value="YT" {{ old('country') == 'YT' ? 'selected' : '' }}>Mayotte</option>
                                                <option value="MX" {{ old('country') == 'MX' ? 'selected' : '' }}>Mexico</option>
                                                <option value="FM" {{ old('country') == 'FM' ? 'selected' : '' }}>Micronesia</option>
                                                <option value="MD" {{ old('country') == 'MD' ? 'selected' : '' }}>Moldova</option>
                                                <option value="MC" {{ old('country') == 'MC' ? 'selected' : '' }}>Monaco</option>
                                                <option value="MN" {{ old('country') == 'MN' ? 'selected' : '' }}>Mongolia</option>
                                                <option value="ME" {{ old('country') == 'ME' ? 'selected' : '' }}>Montenegro</option>
                                                <option value="MS" {{ old('country') == 'MS' ? 'selected' : '' }}>Montserrat</option>
                                                <option value="MA" {{ old('country') == 'MA' ? 'selected' : '' }}>Morocco</option>
                                                <option value="MZ" {{ old('country') == 'MZ' ? 'selected' : '' }}>Mozambique</option>
                                                <option value="MM" {{ old('country') == 'MM' ? 'selected' : '' }}>Myanmar [Burma]</option>
                                                <option value="NA" {{ old('country') == 'NA' ? 'selected' : '' }}>Namibia</option>
                                                <option value="NR" {{ old('country') == 'NR' ? 'selected' : '' }}>Nauru</option>
                                                <option value="NP" {{ old('country') == 'NP' ? 'selected' : '' }}>Nepal</option>
                                                <option value="NL" {{ old('country') == 'NL' ? 'selected' : '' }}>Netherlands</option>
                                                <option value="AN" {{ old('country') == 'AN' ? 'selected' : '' }}>Netherlands Antilles</option>
                                                <option value="NC" {{ old('country') == 'NC' ? 'selected' : '' }}>New Caledonia</option>
                                                <option value="NZ" {{ old('country') == 'NZ' ? 'selected' : '' }}>New Zealand</option>
                                                <option value="NI" {{ old('country') == 'NI' ? 'selected' : '' }}>Nicaragua</option>
                                                <option value="NE" {{ old('country') == 'NE' ? 'selected' : '' }}>Niger</option>
                                                <option value="NG" {{ old('country') == 'NG' ? 'selected' : '' }}>Nigeria</option>
                                                <option value="NU" {{ old('country') == 'NU' ? 'selected' : '' }}>Niue</option>
                                                <option value="NF" {{ old('country') == 'NF' ? 'selected' : '' }}>Norfolk Island</option>
                                                <option value="MP" {{ old('country') == 'MP' ? 'selected' : '' }}>Northern Mariana Islands</option>
                                                <option value="KP" {{ old('country') == 'KP' ? 'selected' : '' }}>North Korea</option>
                                                <option value="NO" {{ old('country') == 'NO' ? 'selected' : '' }}>Norway</option>
                                                <option value="OM" {{ old('country') == 'OM' ? 'selected' : '' }}>Oman</option>
                                                <option value="PK" {{ old('country') == 'PK' ? 'selected' : '' }}>Pakistan</option>
                                                <option value="PW" {{ old('country') == 'PW' ? 'selected' : '' }}>Palau</option>
                                                <option value="PS" {{ old('country') == 'PS' ? 'selected' : '' }}>Palestinian Territories</option>
                                                <option value="PA" {{ old('country') == 'PA' ? 'selected' : '' }}>Panama</option>
                                                <option value="PG" {{ old('country') == 'PG' ? 'selected' : '' }}>Papua New Guinea</option>
                                                <option value="PY" {{ old('country') == 'PY' ? 'selected' : '' }}>Paraguay</option>
                                                <option value="PE" {{ old('country') == 'PE' ? 'selected' : '' }}>Peru</option>
                                                <option value="PH" {{ old('country') == 'PH' ? 'selected' : '' }}>Philippines</option>
                                                <option value="PN" {{ old('country') == 'PN' ? 'selected' : '' }}>Pitcairn Islands</option>
                                                <option value="PL" {{ old('country') == 'PL' ? 'selected' : '' }}>Poland</option>
                                                <option value="PT" {{ old('country') == 'PT' ? 'selected' : '' }}>Portugal</option>
                                                <option value="PR" {{ old('country') == 'PR' ? 'selected' : '' }}>Puerto Rico</option>
                                                <option value="QA" {{ old('country') == 'QA' ? 'selected' : '' }}>Qatar</option>
                                                <option value="RE" {{ old('country') == 'RE' ? 'selected' : '' }}>Réunion</option>
                                                <option value="RO" {{ old('country') == 'RO' ? 'selected' : '' }}>Romania</option>
                                                <option value="RU" {{ old('country') == 'RU' ? 'selected' : '' }}>Russia</option>
                                                <option value="RW" {{ old('country') == 'RW' ? 'selected' : '' }}>Rwanda</option>
                                                <option value="BL" {{ old('country') == 'BL' ? 'selected' : '' }}>Saint Barthélemy</option>
                                                <option value="SH" {{ old('country') == 'SH' ? 'selected' : '' }}>Saint Helena</option>
                                                <option value="KN" {{ old('country') == 'KN' ? 'selected' : '' }}>Saint Kitts and Nevis</option>
                                                <option value="LC" {{ old('country') == 'LC' ? 'selected' : '' }}>Saint Lucia</option>
                                                <option value="MF" {{ old('country') == 'MF' ? 'selected' : '' }}>Saint Martin</option>
                                                <option value="PM" {{ old('country') == 'PM' ? 'selected' : '' }}>Saint Pierre and Miquelon</option>
                                                <option value="VC" {{ old('country') == 'VC' ? 'selected' : '' }}>Saint Vincent and the Grenadines</option>
                                                <option value="WS" {{ old('country') == 'WS' ? 'selected' : '' }}>Samoa</option>
                                                <option value="SM" {{ old('country') == 'SM' ? 'selected' : '' }}>San Marino</option>
                                                <option value="ST" {{ old('country') == 'ST' ? 'selected' : '' }}>São Tomé and Príncipe</option>
                                                <option value="SA" {{ old('country') == 'SA' ? 'selected' : '' }}>Saudi Arabia</option>
                                                <option value="SN" {{ old('country') == 'SN' ? 'selected' : '' }}>Senegal</option>
                                                <option value="RS" {{ old('country') == 'RS' ? 'selected' : '' }}>Serbia</option>
                                                <option value="SC" {{ old('country') == 'SC' ? 'selected' : '' }}>Seychelles</option>
                                                <option value="SL" {{ old('country') == 'SL' ? 'selected' : '' }}>Sierra Leone</option>
                                                <option value="SG" {{ old('country') == 'SG' ? 'selected' : '' }}>Singapore</option>
                                                <option value="SK" {{ old('country') == 'SK' ? 'selected' : '' }}>Slovakia</option>
                                                <option value="SI" {{ old('country') == 'SI' ? 'selected' : '' }}>Slovenia</option>
                                                <option value="SB" {{ old('country') == 'SB' ? 'selected' : '' }}>Solomon Islands</option>
                                                <option value="SO" {{ old('country') == 'SO' ? 'selected' : '' }}>Somalia</option>
                                                <option value="ZA" {{ old('country') == 'ZA' ? 'selected' : '' }}>South Africa</option>
                                                <option value="GS" {{ old('country') == 'GS' ? 'selected' : '' }}>South Georgia</option>
                                                <option value="KR" {{ old('country') == 'KR' ? 'selected' : '' }}>South Korea</option>
                                                <option value="ES" {{ old('country') == 'ES' ? 'selected' : '' }}>Spain</option>
                                                <option value="LK" {{ old('country') == 'LK' ? 'selected' : '' }}>Sri Lanka</option>
                                                <option value="SD" {{ old('country') == 'SD' ? 'selected' : '' }}>Sudan</option>
                                                <option value="SR" {{ old('country') == 'SR' ? 'selected' : '' }}>Suriname</option>
                                                <option value="SJ" {{ old('country') == 'SJ' ? 'selected' : '' }}>Svalbard and Jan Mayen</option>
                                                <option value="SZ" {{ old('country') == 'SZ' ? 'selected' : '' }}>Swaziland</option>
                                                <option value="SE" {{ old('country') == 'SE' ? 'selected' : '' }}>Sweden</option>
                                                <option value="CH" {{ old('country') == 'CH' ? 'selected' : '' }}>Switzerland</option>
                                                <option value="SY" {{ old('country') == 'SY' ? 'selected' : '' }}>Syria</option>
                                                <option value="TW" {{ old('country') == 'TW' ? 'selected' : '' }}>Taiwan</option>
                                                <option value="TJ" {{ old('country') == 'TJ' ? 'selected' : '' }}>Tajikistan</option>
                                                <option value="TZ" {{ old('country') == 'TZ' ? 'selected' : '' }}>Tanzania</option>
                                                <option value="TH" {{ old('country') == 'TH' ? 'selected' : '' }}>Thailand</option>
                                                <option value="TL" {{ old('country') == 'TL' ? 'selected' : '' }}>Timor-Leste</option>
                                                <option value="TG" {{ old('country') == 'TG' ? 'selected' : '' }}>Togo</option>
                                                <option value="TK" {{ old('country') == 'TK' ? 'selected' : '' }}>Tokelau</option>
                                                <option value="TO" {{ old('country') == 'TO' ? 'selected' : '' }}>Tonga</option>
                                                <option value="TT" {{ old('country') == 'TT' ? 'selected' : '' }}>Trinidad and Tobago</option>
                                                <option value="TN" {{ old('country') == 'TN' ? 'selected' : '' }}>Tunisia</option>
                                                <option value="TR" {{ old('country') == 'TR' ? 'selected' : '' }}>Turkey</option>
                                                <option value="TM" {{ old('country') == 'TM' ? 'selected' : '' }}>Turkmenistan</option>
                                                <option value="TC" {{ old('country') == 'TC' ? 'selected' : '' }}>Turks and Caicos Islands</option>
                                                <option value="TV" {{ old('country') == 'TV' ? 'selected' : '' }}>Tuvalu</option>
                                                <option value="UG" {{ old('country') == 'UG' ? 'selected' : '' }}>Uganda</option>
                                                <option value="UA" {{ old('country') == 'UA' ? 'selected' : '' }}>Ukraine</option>
                                                <option value="AE" {{ old('country') == 'AE' ? 'selected' : '' }}>United Arab Emirates</option>
                                                <option value="Uk" {{ old('country') == 'Uk' ? 'selected' : '' }}>United Kingdom</option>
                                                <option value="UY" {{ old('country') == 'UY' ? 'selected' : '' }}>Uruguay</option>
                                                <option value="UM" {{ old('country') == 'UM' ? 'selected' : '' }}>U.S. Minor Outlying Islands</option>
                                                <option value="VI" {{ old('country') == 'VI' ? 'selected' : '' }}>U.S. Virgin Islands</option>
                                                <option value="UZ" {{ old('country') == 'UZ' ? 'selected' : '' }}>Uzbekistan</option>
                                                <option value="VU" {{ old('country') == 'VU' ? 'selected' : '' }}>Vanuatu</option>
                                                <option value="VA" {{ old('country') == 'VA' ? 'selected' : '' }}>Vatican City</option>
                                                <option value="VE" {{ old('country') == 'VE' ? 'selected' : '' }}>Venezuela</option>
                                                <option value="VN" {{ old('country') == 'VN' ? 'selected' : '' }}>Vietnam</option>
                                                <option value="WF" {{ old('country') == 'WF' ? 'selected' : '' }}>Wallis and Futuna</option>
                                                <option value="EH" {{ old('country') == 'EH' ? 'selected' : '' }}>Western Sahara</option>
                                                <option value="YE" {{ old('country') == 'YE' ? 'selected' : '' }}>Yemen</option>
                                                <option value="ZM" {{ old('country') == 'ZM' ? 'selected' : '' }}>Zambia</option>
                                                <option value="ZW" {{ old('country') == 'ZW' ? 'selected' : '' }}>Zimbabwe</option>
                                            </select> --}}
                                            @error('country')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>State<span>*</span></label>
                                            <input type="text" name="state" placeholder="" value="{{old('state')}}">
                                            @error('state')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>City<span>*</span></label>
                                            <input type="text" name="city" placeholder="" value="{{old('city')}}">
                                            @error('city')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Flat/House No., Building/Apartment<span>*</span></label>
                                            <input type="text" name="address1" placeholder="" value="{{old('address1')}}">
                                            @error('address1')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Area, Street, Sector, Town</label>
                                            <input type="text" name="address2" placeholder="" value="{{old('address2')}}">
                                            @error('address2')
                                                <span class='text-danger'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                                <!--/ End Form -->
                            </div>
                        </div>
                        <div class="col-lg-4 col-12">
                            <div class="order-details">
                                
                                
                                <div class="single-widget">
                                    <h2>CART  TOTALS</h2>
                                    <div class="content">
                                        <ul>
										    <li class="order_subtotal" data-price="{{Helper::totalCartPrice()}}">Cart Subtotal<span>₹{{number_format(Helper::totalCartPrice(),2)}}</span></li>
                                            <li class="shipping">
                                                Shipping Cost
                                                <input type="hidden" name="shipping" id="shipping_input" value="0">
                                                <span id="shipping_amount">₹0.00</span>
                                            
                                            </li>
                                            
                                            @if(session('coupon'))
                                            <li class="coupon_price" data-price="{{session('coupon')['value']}}">You Save<span>₹{{number_format(session('coupon')['value'],2)}}</span></li>
                                            @endif
                                            @php
                                                $total_amount = Helper::totalCartPrice() - (session('coupon')['value'] ?? 0);
                                                $gstTotal = Helper::totalGstPrice();
                                            @endphp
                                            @if($gstTotal > 0)
                                                @php $total_amount += $gstTotal; @endphp
                                                <li class="" id="gst_amount">GST Amount<span>₹{{number_format($gstTotal,2)}}</span></li>
                                            @endif
                                            <li class="last" id="order_total_price">
                                                Total <span data-base="{{$total_amount}}">₹{{ number_format($total_amount, 2) }}</span>
                                            </li>


                                        </ul>
                                    </div>
                                </div>
                                <div class="single-widget get-button">
                                    <div class="content">
                                        <div class="button">
                                            <button id="checkout-submit-btn" type="submit" onchange="checkDeliveryService()" class="btn">proceed to checkout</button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
        </div>
    </section>
    <!--/ End Checkout -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('checkout-form');
            var button = document.getElementById('checkout-submit-btn');
            var overlay = document.getElementById('checkout-loader-overlay');
            var isSubmitting = false;

            if (!form || !button || !overlay) {
                return;
            }

            form.addEventListener('submit', function (event) {
                if (isSubmitting) {
                    event.preventDefault();
                    return false;
                }

                var paymentMethod = form.querySelector('input[name="payment_method"]:checked');
                var requiredFields = ['first_name', 'last_name', 'email', 'phone', 'post_code', 'state', 'city', 'address1'];
                var isValid = true;

                if (!paymentMethod) {
                    isValid = false;
                }

                requiredFields.forEach(function (fieldName) {
                    var field = form.querySelector('[name="' + fieldName + '"]');
                    if (!field || !field.value || !field.value.trim()) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    button.disabled = false;
                    button.innerHTML = 'proceed to checkout';
                    overlay.style.display = 'none';
                    return true;
                }

                isSubmitting = true;
                form.setAttribute('data-processing', 'true');

                button.disabled = true;
                button.innerHTML = 'Processing...';
                overlay.style.display = 'flex';
            });
        });
    </script>
    
    <!-- Start Shop Services Area  -->
    <section class="shop-services section home">
        <div class="container">
            <div class="row">
                {{-- <div class="col-lg-3 col-md-6 col-12">
                    <div class="single-service">
                        <i class="ti-rocket"></i>
                        <h4>Free shiping</h4>
                        <p>Orders over ₹100</p>
                    </div>
                </div> --}}
                {{-- <div class="col-lg-3 col-md-6 col-12">
                    <div class="single-service">
                        <i class="ti-reload"></i>
                        <h4>Free Return</h4>
                        <p>Within 30 days returns</p>
                    </div>
                </div> --}}
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
    <!-- End Shop Services -->
    
    <!-- Start Shop Newsletter  -->
    <!--<section class="shop-newsletter section">-->
    <!--    <div class="container">-->
    <!--        <div class="inner-top">-->
    <!--            <div class="row">-->
    <!--                <div class="col-lg-8 offset-lg-2 col-12">-->
                        <!-- Start Newsletter Inner -->
    <!--                    <div class="inner">-->
    <!--                        <h4>Newsletter</h4>-->
    <!--                        <p> Subscribe to our newsletter and get <span>10%</span> off your first purchase</p>-->
    <!--                        <form action="mail/mail.php" method="get" target="_blank" class="newsletter-inner">-->
    <!--                            <input name="EMAIL" placeholder="Your email address" required="" type="email">-->
    <!--                            <button class="btn">Subscribe</button>-->
    <!--                        </form>-->
    <!--                    </div>-->
                        <!-- End Newsletter Inner -->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</section>-->
    <!-- End Shop Newsletter -->
@endsection
@push('styles')
	<style>
		li.shipping{
			display: inline-flex;
			width: 100%;
			font-size: 14px;
		}
		li.shipping .input-group-icon {
			width: 100%;
			margin-left: 10px;
		}
		.input-group-icon .icon {
			position: absolute;
			left: 20px;
			top: 0;
			line-height: 40px;
			z-index: 3;
		}
		.form-select {
			height: 30px;
			width: 100%;
		}
		.form-select .nice-select {
			border: none;
			border-radius: 0px;
			height: 40px;
			background: #f6f6f6 !important;
			padding-left: 45px;
			padding-right: 40px;
			width: 100%;
		}
		.list li{
			margin-bottom:0 !important;
		}
		.list li:hover{
			background:#F7941D !important;
			color:white !important;
		}
		.form-select .nice-select::after {
			top: 14px;
		}
	</style>
@endpush
@push('scripts')
    <script>
        window.APP_URLS = {
            // check service availability for delhivery
            checkserviceavailbilty: "{{ route('check-availbility') }}", 
            checkshipingcharges: "{{ route('check-shiping-charges') }}",
        };
    
    </script>
	<script src="{{asset('public/frontend/js/nice-select/js/jquery.nice-select.min.js')}}"></script>
	<script src="{{ asset('public/frontend/js/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('public/frontend/js/checkouts/checkout.js') }}"></script>
	<script>
		$(document).ready(function() { $("select.select2").select2(); });
  		$('select.nice-select').niceSelect();
	</script>
	<script>
		function showMe(box){
			var checkbox=document.getElementById('shipping').style.display;
			// alert(checkbox);
			var vis= 'none';
			if(checkbox=="none"){
				vis='block';
			}
			if(checkbox=="block"){
				vis="none";
			}
			document.getElementById(box).style.display=vis;
		}
	</script>
	<script>
		$(document).ready(function(){
// 			$('.shipping select[name=shipping]').change(function(){
// 				let cost = parseFloat( $(this).find('option:selected').data('price') ) || 0;
// 				let subtotal = parseFloat( $('.order_subtotal').data('price') );  
// 				let coupon = parseFloat( $('.coupon_price').data('price') ) || 0; 
// 				// alert(coupon);
// 				$('#order_total_price span').text('₹'+(subtotal + cost-coupon).toFixed(2));
// 			});

		});
 
	</script>

@endpush