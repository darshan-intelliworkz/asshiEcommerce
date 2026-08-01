@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || My Profile')

@section('main-content')
    <!-- Premium Profile Section -->
    <section class="user-profile-section py-5" style="background-color: #f8f9fa;">
        <div class="container">
            <div class="row">
                <!-- Sidebar Navigation -->
                <div class="col-lg-4 col-md-5 mb-4">
                    <div class="card profile-sidebar shadow rounded" style="border: none;">
                        <div class="card-body text-center p-4" style="background: #333333; color: white; border-top-left-radius: 0.25rem; border-top-right-radius: 0.25rem;">
                            <div class="profile-avatar mb-3 position-relative d-inline-block">
                                @php 
                                    $dummy_image = 'https://bootdey.com/img/Content/avatar/avatar7.png';
                                    $photo = $profile->photo;
                                    if ($photo) {
                                        $avatar = (strpos($photo, 'http') === 0) ? $photo : asset($photo);
                                    } else {
                                        $avatar = $dummy_image;
                                    }
                                @endphp
                                <img id="holder" src="{{$avatar}}" alt="Profile Image" class="rounded-circle shadow" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid rgba(255,255,255,0.3); background-color: #fff;">
                            </div>
                            <h4 class="mb-0 font-weight-bold text-white">{{$profile->name}}</h4>
                            <p class="mb-2" style="color: #cccccc;"><i class="ti-email mr-1"></i> {{$profile->email}}</p>
                        </div>
                        <div class="list-group list-group-flush profile-nav nav nav-pills" id="profile-tabs" role="tablist" aria-orientation="vertical">
                            <a class="list-group-item list-group-item-action active d-flex align-items-center px-4 py-3 border-0" id="profile-details-tab" data-toggle="pill" href="#profile-details" role="tab" aria-controls="profile-details" aria-selected="true" style="cursor:pointer;">
                                <i class="ti-user mr-3"></i> My Profile
                            </a>
                            <a class="list-group-item list-group-item-action d-flex align-items-center px-4 py-3 border-0 text-muted" id="change-password-tab" data-toggle="pill" href="#change-password" role="tab" aria-controls="change-password" aria-selected="false" style="cursor:pointer;">
                                <i class="ti-key mr-3"></i> Change Password
                            </a>
                            <a class="list-group-item list-group-item-action d-flex align-items-center px-4 py-3 border-0 text-muted" id="my-orders-tab" data-toggle="pill" href="#my-orders" role="tab" aria-controls="my-orders" aria-selected="false" style="cursor:pointer;">
                                <i class="ti-shopping-cart mr-3"></i> My Orders
                            </a>
                            <a href="{{route('user.logout')}}" class="list-group-item list-group-item-action d-flex align-items-center px-4 py-3 border-0 text-danger">
                                <i class="ti-power-off mr-3"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Profile Content -->
                <div class="col-lg-8 col-md-7">
                    <div class="tab-content" id="profile-tabsContent">
                        
                        <!-- Profile Tab -->
                        <div class="tab-pane fade show active" id="profile-details" role="tabpanel" aria-labelledby="profile-details-tab">
                            <div class="card profile-content shadow rounded" style="border: none;">
                                <div class="card-header bg-white pt-4 pb-2 px-4" style="border-bottom: 1px solid #eee;">
                                    <h3 class="font-weight-bold text-dark mb-0">Personal Information</h3>
                                    <p class="text-muted small">Update your personal details and settings here.</p>
                                </div>
                                <div class="card-body p-4 shop login">
                                    <form class="form" method="post" action="{{route('user-profile-update', $profile->id)}}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6 col-12">
                                                <div class="form-group mb-4">
                                                    <label style="color: #333; font-weight: 500;">Full Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" placeholder="Enter your full name" required="required" value="{{$profile->name}}" style="width: 100%; height: 45px; border-radius: 0; padding: 0 20px;">
                                                    @error('name')
                                                        <span class="text-danger small">{{$message}}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 col-12">
                                                <div class="form-group mb-4">
                                                    <label style="color: #333; font-weight: 500;">Email Address <span class="text-danger">*</span></label>
                                                    <input type="email" name="email" placeholder="Enter your email" disabled value="{{$profile->email}}" style="width: 100%; height: 45px; border-radius: 0; padding: 0 20px; background-color: #f5f5f5;">
                                                    <small class="form-text text-muted" style="margin-top: 5px;">Your email address cannot be changed.</small>
                                                    @error('email')
                                                        <span class="text-danger small">{{$message}}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-12 col-12">
                                                <div class="form-group mb-5">
                                                    <label style="color: #333; font-weight: 500;">Profile Photo URL</label>
                                                    <div style="display: flex; gap: 10px; align-items: center;">
                                                        <label class="btn" style="background: #333; color: white; padding: 12px 20px; text-transform: uppercase; font-size: 14px; font-weight: 500; border-radius: 0; cursor: pointer; margin-bottom: 0;">
                                                            <i class="fa fa-picture-o"></i> Choose Image
                                                            <input type="file" name="photo" accept="image/*" style="display: none;" onchange="document.getElementById('thumbnail').value = this.files[0].name; var reader = new FileReader(); reader.onload = function(e) { document.getElementById('holder').src = e.target.result; }; reader.readAsDataURL(this.files[0]);">
                                                        </label>
                                                        <input id="thumbnail" type="text" value="{{$profile->photo}}" readonly style="flex: 1; height: 45px; border: 1px solid #e6e2f5; padding: 0 20px; background-color: #f5f5f5;">
                                                    </div>
                                                    @error('photo')
                                                        <span class="text-danger small">{{$message}}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 border-top pt-4">
                                                <div class="form-group login-btn mb-0 text-right">
                                                    <button class="btn" type="submit" style="background: #F7941D; color: white; border-radius: 0; text-transform: uppercase; padding: 12px 30px; font-weight: 600;">
                                                        Save Changes <i class="ti-arrow-right ml-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Quick Stats Cards -->
                            <div class="row mt-4">
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow rounded bg-white h-100 p-3" style="border: none; display: flex; flex-direction: row; align-items: center;">
                                        <div class="mr-3" style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                            <i class="ti-package" style="font-size: 20px; color: #F7941D;"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0" style="color: #333; font-weight: 600;">My Orders</h5>
                                            <a href="{{route('user.order.index')}}" class="text-muted small" style="text-decoration: none;">View all your orders</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card shadow rounded bg-white h-100 p-3" style="border: none; display: flex; flex-direction: row; align-items: center;">
                                        <div class="mr-3" style="width: 50px; height: 50px; background: #f8f9fa; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                            <i class="ti-heart" style="font-size: 20px; color: #dc3545;"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0" style="color: #333; font-weight: 600;">Wishlist</h5>
                                            <a href="{{route('wishlist')}}" class="text-muted small" style="text-decoration: none;">Items you loved</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password Tab -->
                        <div class="tab-pane fade" id="change-password" role="tabpanel" aria-labelledby="change-password-tab">
                            <div class="card profile-content shadow rounded" style="border: none;">
                                <div class="card-header bg-white pt-4 pb-2 px-4" style="border-bottom: 1px solid #eee;">
                                    <h3 class="font-weight-bold text-dark mb-0">Change Password</h3>
                                    <p class="text-muted small">Update your account password securely.</p>
                                </div>
                                <div class="card-body p-4 shop login">
                                    <form class="form" method="POST" action="{{ route('change.password') }}">
                                        @csrf
                                        <div class="row">
                                            @if($errors->any())
                                                @foreach ($errors->all() as $error)
                                                    <div class="col-12"><p class="text-danger mb-3">{{ $error }}</p></div>
                                                @endforeach 
                                            @endif
                                            
                                            <div class="col-12">
                                                <div class="form-group mb-4">
                                                    <label style="color: #333; font-weight: 500;">Current Password <span class="text-danger">*</span></label>
                                                    <div style="position: relative;">
                                                        <input type="password" name="current_password" id="current_password" placeholder="Enter current password" required="required" autocomplete="current-password" style="width: 100%; height: 45px; border-radius: 0; padding: 0 20px; padding-right: 45px;">
                                                        <span onclick="togglePassword('current_password', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; color: #666;">
                                                            <i class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="form-group mb-4">
                                                    <label style="color: #333; font-weight: 500;">New Password <span class="text-danger">*</span></label>
                                                    <div style="position: relative;">
                                                        <input type="password" name="new_password" id="new_password" placeholder="Enter new password" required="required" autocomplete="new-password" style="width: 100%; height: 45px; border-radius: 0; padding: 0 20px; padding-right: 45px;">
                                                        <span onclick="togglePassword('new_password', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; color: #666;">
                                                            <i class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="form-group mb-5">
                                                    <label style="color: #333; font-weight: 500;">Confirm New Password <span class="text-danger">*</span></label>
                                                    <div style="position: relative;">
                                                        <input type="password" name="new_confirm_password" id="new_confirm_password" placeholder="Confirm new password" required="required" autocomplete="new-password" style="width: 100%; height: 45px; border-radius: 0; padding: 0 20px; padding-right: 45px;">
                                                        <span onclick="togglePassword('new_confirm_password', this)" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; color: #666;">
                                                            <i class="fa fa-eye"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 border-top pt-4">
                                                <div class="form-group login-btn mb-0 text-right">
                                                    <button class="btn" type="submit" style="background: #F7941D; color: white; border-radius: 0; text-transform: uppercase; padding: 12px 30px; font-weight: 600;">
                                                        Update Password <i class="ti-lock ml-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- My Orders Tab -->
                        <div class="tab-pane fade" id="my-orders" role="tabpanel" aria-labelledby="my-orders-tab">
                            <div class="card profile-content shadow rounded" style="border: none;">
                                <div class="card-header bg-white pt-4 pb-2 px-4" style="border-bottom: 1px solid #eee;">
                                    <h3 class="font-weight-bold text-dark mb-0">My Orders</h3>
                                    <p class="text-muted small">View and track all your recent orders.</p>
                                </div>
                                <div class="card-body p-4 shop login">
                                    <div class="table-responsive">
                                        @if(count($orders)>0)
                                        <table class="table shopping-summery table-hover table-bordered" style="font-size: 14px;">
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
                                                @foreach($orders as $order)
                                                    <tr>
                                                        <td class="align-middle">{{$order->order_number}}</td>
                                                        <td class="align-middle">₹{{number_format($order->total_amount,2)}}</td>
                                                        <td class="align-middle">
                                                            @if($order->status=='new')
                                                            <span class="badge badge-primary order-badge">{{strtoupper($order->status)}}</span>
                                                            @elseif($order->status=='process')
                                                            <span class="badge badge-warning order-badge">{{strtoupper($order->status)}}</span>
                                                            @elseif($order->status=='delivered')
                                                            <span class="badge badge-success order-badge">{{strtoupper($order->status)}}</span>
                                                            @else
                                                            <span class="badge badge-danger order-badge">{{strtoupper($order->status)}}</span>
                                                            @endif
                                                        </td>
                                                        <td class="align-middle">
                                                            {{strtoupper($order->payment_status) ?? '-'}}
                                                        </td>
                                                        <td class="align-middle text-center">
                                                            <a href="{{ route('order.dertails',$order->id )}}" class="btn btn-sm btn-info btn-order-view"><i class="ti-eye"></i> View</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="mt-4" style="display: flex; justify-content: flex-end;">
                                            {{$orders->links()}}
                                        </div>
                                        @else
                                        <div class="text-center py-5">
                                            <i class="ti-shopping-cart mb-3" style="font-size: 40px; color: #ccc;"></i>
                                            <h5 class="text-muted">No orders found!</h5>
                                            <p class="text-muted">You haven't placed any orders yet.</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
<style>
    /* Order Badges & Buttons */
    .order-badge { padding: 5px 10px; border-radius: 12px; font-size: 0.85rem; }
    .badge-success { background-color: #5db845 !important; color: white; }
    .badge-primary { background-color: #007bff !important; color: white; }
    .badge-warning { background-color: #ffc107 !important; color: #212529; }
    .badge-danger  { background-color: #dc3545 !important; color: white; }
    .btn-order-view { background-color: #5db844 !important; color: white !important; border: none; padding: 5px 10px; border-radius: 8px; font-size: 0.85rem; transition: 0.3s; }

    .table td, .table th {
        vertical-align: middle;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .rounded-lg {
        border-radius: 12px !important;
    }
    .profile-nav .list-group-item {
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .profile-nav .list-group-item:hover {
        background-color: #f8f9fa;
        padding-left: 2rem !important;
    }
    .profile-nav .list-group-item.active {
        background-color: transparent;
        color: #28a745 !important;
        border-left: 4px solid #28a745 !important;
        font-weight: 600;
    }
    .input-group-text {
        background-color: #f8f9fa;
    }
    .form-control:focus {
        border-color: #28a745;
        box-shadow: none;
    }
    .icon-box {
        transition: all 0.3s ease;
    }
    .icon-box:hover {
        background-color: #e9ecef !important;
        transform: scale(1.05);
    }
</style>
@endpush

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<script>
    $('#lfm').filemanager('image');
    
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        $('.dltBtn').click(function(e){
            var form=$(this).closest('form');
            var dataID=$(this).data('id');
            e.preventDefault();
            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to recover this data!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    form.submit();
                } else {
                    swal("Your order is safe!");
                }
            });
        });
    });

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