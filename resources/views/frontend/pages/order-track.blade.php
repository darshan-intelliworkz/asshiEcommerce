@extends('frontend.layouts.master')

@section('title','Aashi-Ecommerce || Order Track Page')

@section('main-content')
<style>
    :root {
        --green: #5db845;
        --muted: #DDDDDD;
        --text: #333;
        --bg: #fff;
        /* tracker card background */
        --white: #fff;
        /* utility white */
        --page-bg: #f7f8fa;
        /* page background */
        --muted-text: #666;
        /* titles */
        --label-color: #888;
        /* small labels */
        --control-border: #e6e6e6;
        /* control button border */
        --shadow-lg: 0 6px 18px rgba(18, 24, 40, 0.06);
        --shadow-sm: 0 2px 6px rgba(16, 24, 40, 0.06);
        --step-size: 44px;
        /* reduced icon size */
    }
    .tracker {
        /* max-width: 900px; */
        /* margin: 24px auto; */
        padding: 28px;
        background: var(--bg);
        border-radius: 10px;
        box-shadow: var(--shadow-lg);
        border:1px solid var(--text);
        
    }

    .tracker h2 {
        margin: 0 0 12px 0;
        font-size: 28px;
       text-align: center;
    }

    .step {
        cursor: pointer;
        
    }

    .steps {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        padding: 20px 10px;
          
    }

    /* line that sits behind the steps */
    .steps::before {
        content: "";
        position: absolute;
        left: calc(var(--step-size)/2 + 10px);
        right: calc(var(--step-size)/2 + 10px);
        height: 4px;
        background: linear-gradient(90deg, var(--green) 0%, var(--green) var(--progress), var(--muted) var(--progress));
        border-radius: 4px;
        top: calc(50% - 11px);
        z-index: 0
    }

    .step {
        flex: 1;
        position: relative;
        text-align: center;
        padding: 0 12px;
        z-index: 1
    }

    .step .icon {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--muted);
        color: var(--white);
        font-weight: 700;
        margin: 20px 0;
        box-shadow: var(--shadow-sm);
    }

    .step.completed .icon {
        background: var(--green);
         rotate: 320deg;
    }

    .step.active .icon {
        background: var(--white);
        border: 3px solid var(--green);
        color: var(--green);
         rotate: 320deg;
    }

    .step.pending .icon {
        background: var(--muted);
        color: var(--white);
        opacity: .9;
         rotate: 320deg;
    }

    .step .title {
        font-size: 13px;
        color: var(--muted-text);
    }

    /* small icons inside the circle - use svg icons size */
    .icon svg {
        width: 20px;
        height: 20px;
        display: block
    }
    /* responsive adjustments */
    @media (max-width:520px) {
        .step .title {
            font-size: 11px
        }

        .icon {
            width: 40px;
            height: 40px
        }

        :root {
            --step-size: 40px
        }

        .icon svg {
            width: 18px;
            height: 18px
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
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0);">Order Track</a></li>
                        </ul>
                    </div>
                </div> 
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->
<section class="tracking_box_area section_gap py-5">
    <div class="container">
        <div class="tracking_box_inner">
            <p>To track your order please enter your Order ID in the box below and press the "Track" button. This was given
                to you on your receipt and in the confirmation email you should have received.</p>
            <form class="row tracking_form my-4" id="trackOrderForm">
              @csrf
                <div class="col-md-10 form-group">
                    <select class="form-control p-2 select"  name="order_number" id="order_number">
                        <option value="">-Select Order Number-</option>
                        @if(isset($orders) && is_countable($orders) && count($orders) > 0)
                            @foreach ($orders as $key => $val )
                                <option value="{{ $val->order_number }}">{{$val->order_number}}</option>                                
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-8 form-group">
                    <button type="submit" class="btn submit_btn">Track Order</button>
                </div>
            </form>
        </div>

        <div class="tracker d-none" id="orderTracker" aria-label="Order progress tracker">
            <h2 style="margin:0 0 12px 0;font-size:18px">Order Status</h2>
            <div class="steps" id="steps" style="--progress:0%">

                <div class="step completed" data-step="1">
                    <div class="icon" aria-hidden="true">
                        <!-- check -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                    </div>
                    <div class="title">Order Placed</div>
                </div>

                <div class="step completed" data-step="2">
                    <div class="icon" aria-hidden="true">
                        <!-- check -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                    </div>
                    <div class="title">In Process</div>
                </div>

                <div class="step active" data-step="3">
                    <div class="icon" aria-hidden="true">
                        <!-- check -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                    </div>
                    <div class="title">Out For Delivery</div>
                </div>

                <div class="step pending" data-step="4">
                    <div class="icon" aria-hidden="true">
                        <!-- check -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M20 6L9 17l-5-5"></path>
                        </svg>
                    </div>
                    <div class="title">Delivered</div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
/* ---------- STEP TRACKER FUNCTION (GLOBAL) ---------- */
function setStep(n) {
    const steps = document.querySelectorAll('.step');
    const stepsEl = document.getElementById('steps');

    steps.forEach((el, i) => {
        el.classList.remove('completed', 'active', 'pending');

        if (i + 1 < n) el.classList.add('completed');
        else if (i + 1 === n) el.classList.add('active');
        else el.classList.add('pending');
    });

    const percent = ((n - 1) / (steps.length - 1)) * 100;
    stepsEl.style.setProperty('--progress', percent + '%');
}

/* ---------- FORM SUBMIT ---------- */
$('#trackOrderForm').on('submit', function (e) {
    e.preventDefault();

    let orderNumber = $('#order_number').val();
    if (!orderNumber || orderNumber == '') {
        alert('Please select order number');
        return;
    }

    $.ajax({
        url: "{{ route('product.track.order') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            order_number: orderNumber
        },
        success: function (res) {
            console.log("RES - " + JSON.stringify(res));
            if (res.status === 'error') {
                alert(res.message);
                $('#orderTracker').addClass('d-none');
                return;
            }

            // Show tracker
            $('#orderTracker').removeClass('d-none');

            let step = 1;

            switch (res.order_status) {
                case 'new':
                    step = 1;
                    break;
                case 'process':
                    step = 2;
                    break;
                case 'out for delivery':
                    step = 3;
                    break;
                case 'delivered':
                    step = 4;
                    break;
                default:
                    alert('Something went Wrong');
                    $('#orderTracker').addClass('d-none');
                    return;
            }

            setStep(step);
        },
        error: function (xhr) {
            alert(xhr.responseJSON?.message || 'Something went wrong');
        }
    });
});
</script>
@endpush