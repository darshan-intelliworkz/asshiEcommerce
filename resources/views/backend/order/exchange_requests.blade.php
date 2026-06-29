@extends('backend.layouts.master')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Pending Exchange Requests</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($exchangeRequests)>0)
        <table class="table table-bordered" id="order-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>S.N.</th>
              <th>Order No.</th>
              <th>Name</th>
              <th>Product Details</th>
              <th>Reason</th>
              <th>Images</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($exchangeRequests as $request)  
                <tr>
                    <td>{{$request->id}}</td>
                    <td>
                        <a href="{{route('order.show', $request->order->id)}}">{{$request->order->order_number ?? '-'}}</a>
                    </td>
                    <td>{{$request->order->first_name ?? ''}} {{$request->order->last_name ?? ''}}</td>
                    <td>
                        @if($request->cart)
                            <strong>{{ $request->cart->product->title ?? 'N/A' }}</strong><br>
                            <small>Qty: {{ $request->cart->quantity }} | Color: {{ $request->cart->color->color_name ?? 'N/A' }}</small>
                        @endif
                    </td>
                    <td>
                        {{ $request->reason }}
                        @if($request->customer_comment)
                            <br><small>Note: {{ $request->customer_comment }}</small>
                        @endif
                    </td>
                    <td>
                        @if(is_array($request->images) && count($request->images))
                            @foreach($request->images as $image)
                                <a href="{{ asset('public/'.$image) }}" target="_blank">
                                    <img src="{{ asset('public/'.$image) }}" width="45" height="45" style="object-fit:cover; border-radius:4px; margin:2px;" alt="Image">
                                </a>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($request->status == 'exchange_requested' || $request->status == 'pending')
                            <span class="badge badge-warning">Pending Exchange Request</span>
                        @elseif($request->status == 'exchange_approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif($request->status == 'exchange_rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @else
                            <span class="badge badge-info">{{ ucwords(str_replace('_', ' ', $request->status)) }}</span>
                        @endif
                    </td>
                    <td style="min-width: 200px;">
                        @if($request->status == 'exchange_requested' || $request->status == 'pending')
                            <form method="POST" action="{{ route('exchange-request.approve', $request->id) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success mb-1" onclick="return confirm('Are you sure you want to approve this exchange? This will initiate the pickup and create a replacement shipment.')">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('exchange-request.reject', $request->id) }}">
                                @csrf
                                <textarea name="admin_comment" rows="2" class="form-control mb-1" placeholder="Reject reason (optional)" style="font-size: 12px;"></textarea>
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this exchange request?')">Reject</button>
                            </form>
                        @else
                            -
                        @endif
                    </td>
                </tr>  
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{$exchangeRequests->links()}}</span>
        @else
          <h6 class="text-center">No pending exchange requests found!!!</h6>
        @endif
      </div>
    </div>
</div>
@endsection

@push('styles')
  <link href="{{asset('public/backend/vendor/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
  <style>
      div.dataTables_wrapper div.dataTables_paginate{
          display: none;
      }
  </style>
@endpush

@push('scripts')
  <script src="{{asset('public/backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('public/backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <script src="{{asset('public/backend/js/demo/datatables-demo.js')}}"></script>
  <script>
      $('#order-dataTable').DataTable({
        info: false,
        order: [[0, 'desc']],
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[6]
                }
            ]
        });
  </script>
@endpush
