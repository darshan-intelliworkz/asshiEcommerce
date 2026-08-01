@extends('frontend.layouts.master')
@section('title','Aashi-Ecommerce || My Orders')
@section('main-content')

<!-- Breadcrumbs -->
<div class="breadcrumbs">
   <div class="container">
      <div class="row">
         <div class="col-12">
            <div class="bread-inner">
               <ul class="bread-list">
                  <li><a href="{{ route('home') }}">Home<i class="ti-arrow-right"></i></a></li>
                  <li class="active"><a href="javascript:void(0);">My Orders</a></li>
               </ul>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Orders Section -->
<div class="shopping-cart section">
   <div class="container">
      <div class="row">
         <div class="col-12">

            <table class="table shopping-summery table-hover table-bordered">
               <thead class="thead-light">
                  <tr class="main-hading">
                    <th>Order No.</th>
                    <th>Total Amount</th>
                    <th>Order Status</th>
                    <th>Payment Status</th>
                     <th class="text-center">Order View</th>
                  </tr>
               </thead>
               <tbody>
                @if(isset($orders) && is_countable($orders) && count($orders))
                    @foreach($orders as $order)
                    <tr>
                        <td>{{$order->order_number}}</td>
                        <td>₹{{number_format($order->total_amount,2)}}</td>
                        <td>
                           <span class="badge badge-info">{{strtoupper($order->status)}}</span>
                        </td>
                        <td>
                           <span class="badge badge-dark">{{strtoupper($order->payment_status) ?? '-'}}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('order.dertails',$order->id )}}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i> View</a>
                        </td>
                    </tr>
                    @endforeach
                 @endif
               </tbody>
            </table>

         </div>
      </div>
   </div>
</div>

<style>
/* Center table cells */
.table td, .table th { vertical-align: middle; }

/* Status badges */
.badge-success { background-color: #5db845; color: white; padding: 5px 10px; border-radius: 12px; font-size: 0.85rem; }
.badge-primary { background-color: #007bff; color: white; padding: 5px 10px; border-radius: 12px; font-size: 0.85rem; }
.badge-warning { background-color: #ffc107; color: #212529; padding: 5px 10px; border-radius: 12px; font-size: 0.85rem; }
.badge-danger  { background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 12px; font-size: 0.85rem; }

/* View button */
.btn-info { background-color: #5db844;    color: white !important; border: none; padding: 5px 10px; border-radius: 8px; font-size: 0.85rem; transition: 0.3s; }

</style>

@endsection
