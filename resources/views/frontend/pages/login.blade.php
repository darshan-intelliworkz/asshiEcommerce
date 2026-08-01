@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || Login Page')

@section('main-content')
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0);">Login</a></li>
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
                        <h2>Login</h2>
                        <p>Please register in order to checkout more quickly</p>
                        <!-- Form -->
                        <form class="form" method="post" action="{{route('login.submit')}}">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Your Email<span>*</span></label>
                                        <input type="email" name="email" placeholder="" required="required" value="{{old('email')}}">
                                        @error('email')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Your Password<span>*</span></label>
                                        <div style="position: relative;">
                                            <input type="password" name="password" id="login_password" placeholder="" required="required" value="{{old('password')}}" style="padding-right: 45px;">
                                            <span onclick="togglePassword('login_password', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; color: #666;">
                                                <i class="fa fa-eye"></i>
                                            </span>
                                        </div>
                                        @error('password')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                                        <div class="checkbox m-0">
                                            <label class="checkbox-inline m-0" for="2" style="cursor: pointer;">
                                                <input name="news" id="2" type="checkbox" style="margin-right: 5px;"> Remember me
                                            </label>
                                        </div>
                                        @if (Route::has('password.request'))
                                            <a class="lost-pass m-0" href="{{ route('password.request') }}" style="color: #5db845; text-decoration: none;">
                                                Lost your password?
                                            </a>
                                        @endif
                                    </div>
                                    <div class="form-group login-btn mb-3">
                                        <button class="btn" type="submit" style="width: 100%; border-radius: 0;">Login</button>
                                    </div>
                                    <div class="text-center mt-3">
                                        <span style="color: #666;">Don't have an account?</span> 
                                        <a href="{{route('register.form')}}" style="color: #5db845; font-weight: 600; text-decoration: none; margin-left: 5px;">
                                            Register here
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
@push('styles')
<style>
    .shop.login .form .btn{
        margin-right:0;
    }
    .btn-facebook{
        background:#39579A;
    }
    .btn-facebook:hover{
        background:#073088 !important;
    }
    .btn-github{
        background:#444444;
        color:white;
    }
    .btn-github:hover{
        background:black !important;
    }
    .btn-google{
        background:#ea4335;
        color:white;
    }
    .btn-google:hover{
        background:rgb(243, 26, 26) !important;
    }
</style>
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