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
      <h6 class="m-0 font-weight-bold text-primary float-left">Return Delivered Orders (Ready for Refund)</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($returnRequests)>0)
        <table class="table table-bordered" id="order-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>S.N.</th>
              <th>Order No.</th>
              <th>Name</th>
              <th>Type</th>
              <th>Refund Amount</th>
              <th>Payment Method</th>
              <th>Refund Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($returnRequests as $returnRequest)  
                <tr>
                    <td>{{$returnRequest->id}}</td>
                    <td>{{$returnRequest->order->order_number ?? '-'}}</td>
                    <td>{{$returnRequest->order->first_name ?? ''}} {{$returnRequest->order->last_name ?? ''}}</td>
                    <td>{{ucfirst($returnRequest->return_type)}}</td>
                    <td>₹{{number_format($returnRequest->order->total_amount ?? 0,2)}}</td>
                    <td>
                        {{$returnRequest->order->payment_method == 'cod' ? 'Cash on Delivery' : 'Online (Razorpay)'}}
                        @if($returnRequest->order->payment_method == 'cod' && $returnRequest->return_type == 'return')
                           <br><small>UPI: {{$returnRequest->customer_upi_id ?? 'N/A'}}</small>
                        @endif
                    </td>
                    <td>
                        @if($returnRequest->refund_status == 'processed')
                            <span class="badge badge-success">Processed</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($returnRequest->return_type == 'return' && $returnRequest->refund_status != 'processed')
                            @if($returnRequest->order->payment_method == 'razorpay')
                                <form action="{{ route('return-request.refund', $returnRequest->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to process Razorpay refund?')">Process Refund</button>
                                </form>
                            @elseif($returnRequest->order->payment_method == 'cod')
                                <form action="{{ route('return-request.update-cod-refund', $returnRequest->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="refund_status" value="processed">
                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Have you manually refunded this amount? Mark as processed?')">Mark Refund Processed</button>
                                </form>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>  
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{$returnRequests->links()}}</span>
        @else
          <h6 class="text-center">No return delivered orders found!!!</h6>
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
  <!-- Page level plugins -->
  <script src="{{asset('public/backend/vendor/datatables/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('public/backend/vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="{{asset('public/backend/js/demo/datatables-demo.js')}}"></script>
  <script>
      $('#order-dataTable').DataTable({
        info: false,
        order: [[0, 'desc']],
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[7]
                }
            ]
        });
  </script>
@endpush
