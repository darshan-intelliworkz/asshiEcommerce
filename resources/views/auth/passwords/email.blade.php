@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || Forgot Password')

@section('main-content')
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0);">Forgot Password</a></li>
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
                        <h2>Forgot Password</h2>
                        <p>Please enter your email address to request a password reset link.</p>

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <!-- Form -->
                        <form class="form" method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Your Email<span>*</span></label>
                                        <input type="email" name="email" placeholder="" required="required" value="{{ old('email') }}" autofocus>
                                        @error('email')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group login-btn mb-3">
                                        <button class="btn" type="submit" style="width: 100%; border-radius: 0;">Send Reset Link</button>
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
