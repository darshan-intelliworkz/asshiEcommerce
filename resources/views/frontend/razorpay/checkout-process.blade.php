<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h2>Processing Payment for Order #{{ $order->order_number }}</h2>
    <div id="payment-loader" style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(255,255,255,0.9);
        z-index:9999;
        text-align:center;
        padding-top:20%;
        font-size:18px;
        font-weight:bold;
    "> Please wait, confirming payment...  
        Do not refresh or close the page.
    </div>
 
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var paymentProcessing = false;

        var options = {
            "key": "{{ $key }}",
            "amount": "{{ $amount }}",
            "currency": "INR",
            "name": "Your Store",
            "description": "Order Payment",
            "order_id": "{{ $order_id }}",
            "handler": function (response){ 
                paymentProcessing = true;
                document.getElementById('payment-loader').style.display = 'block';
                fetch("{{ route('razorpay.verify') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature,
                        order_number: "{{ $order->order_number }}"
                    })
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {  
                    document.getElementById('payment-loader').style.display = 'none'; // hide loader
                    if (data.status) {
                        window.location.href = "{{ route('myorders') }}";
                    } else {
                        alert(data.message || 'Verification failed');
                        window.location.href = "{{ route('myorders') }}";
                    }
                })
                .catch(err => { 
                    console.error(err);
                    alert('Payment received but confirmation failed. Please contact support.');
                    window.location.href = "{{ route('myorders') }}";
                });
            },
            "modal": {
                escape: false,
                backdropclose: false,
                "ondismiss": function () {
                    // User closed payment popup
                    fetch("{{ route('razorpay.cancel') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            order_number: "{{ $order->order_number }}"
                        })
                    }).then(() => {
                        window.location.href = "{{ route('razorpay.failed', ['order_number' => $order->order_number]) }}";
                    });
                }
            }
        };
        var rzp = new Razorpay(options);
        rzp.open();
    </script>
</body>
</html>
