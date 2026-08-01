@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || Thank You')

@section('main-content')
    <div class="container" style="padding: 60px 15px;">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 col-12">
                <div style="background:#fff; border:1px solid #e9e9e9; border-radius:10px; padding:40px 30px; text-align:center; box-shadow:0 5px 25px rgba(0,0,0,0.06);">
                    <div style="width:80px; height:80px; border-radius:50%; background:#28a745; color:#fff; display:flex; align-items:center; justify-content:center; font-size:36px; margin:0 auto 20px;">
                        ✓
                    </div>
                    <h2 style="margin-bottom:12px;">Thank You for Your Order!</h2>
                    <p style="font-size:16px; color:#666; margin-bottom:20px;">
                        Your order has been placed successfully. We will process it shortly.
                    </p>
                    @if($order)
                        <p style="margin-bottom:8px;"><strong>Order Number:</strong> {{ $order->order_number }}</p>
                        <p style="margin-bottom:8px;"><strong>Payment Method:</strong> {{ strtoupper($order->payment_method) }}</p>
                        <p style="margin-bottom:24px;"><strong>Total Amount:</strong> ₹{{ number_format($order->total_amount, 2) }}</p>
                    @endif
                    {{-- <a href="{{ route('home') }}" class="btn" style="background:#f7941d; color:#fff; padding:12px 24px; border-radius:4px; text-decoration:none; display:inline-block;">Continue Shopping</a> --}}
                    <a href="{{ route('myorders') }}" class="btn" style="background:#333; color:#fff; padding:12px 24px; border-radius:4px; text-decoration:none; display:inline-block; margin-left:10px;">View Orders</a>
                </div>
            </div>
        </div>
    </div>
@endsection
