@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || Reset Password')

@section('main-content')
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0);">Reset Password</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->

    <!-- Shop Login -->
    <section class="shop login section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 col-12">
                    <div class="login-form">
                        <h2>Reset Password</h2>
                        <p>Please enter your new password to reset it.</p>

                        <!-- Form -->
                        <form class="form" method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token ?? '' }}">

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Your Email<span>*</span></label>
                                        <input type="email" name="email" placeholder="" required="required" value="{{ $email ?? old('email') }}" readonly style="background-color: #f6f6f6;">
                                        @error('email')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>New Password<span>*</span></label>
                                        <div style="position: relative;">
                                            <input type="password" name="password" id="reset_password" placeholder="" required="required" style="padding-right: 45px;">
                                            <span onclick="togglePassword('reset_password', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; color: #666;">
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                        @error('password')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Confirm Password<span>*</span></label>
                                        <div style="position: relative;">
                                            <input type="password" name="password_confirmation" id="reset_password_conf" placeholder="" required="required" style="padding-right: 45px;">
                                            <span onclick="togglePassword('reset_password_conf', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; color: #666;">
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group login-btn mb-3">
                                        <button class="btn" type="submit" style="width: 100%; border-radius: 0;">Reset Password</button>
                                    </div>
                                    <div class="text-center mt-3">
                                        <span style="color: #666;">Remember your password?</span>
                                        <a href="{{route('login.form')}}" style="color: #5db845; font-weight: 600; text-decoration: none; margin-left: 5px;">
                                            Login here
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!--/ End Form -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--/ End Login -->
@endsection

@push('scripts')
<script>
    function togglePassword(inputId, iconSpan) {
        var input = document.getElementById(inputId);
        var icon = iconSpan.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>
@endpush
