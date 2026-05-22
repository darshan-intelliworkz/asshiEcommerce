$(document).ready(function () {

    var shippingAmount = 50;
    if($('.order_subtotal').data('price') == 0){
        shippingAmount = 0;
    }
    $("#shipping_amount").text("₹ " + shippingAmount);
    $("#shipping_input").val(shippingAmount);
    updateTotalPrice(shippingAmount);

    let lastCheckedPin = null;

    $('#post_code').on('keyup change blur', function () {
        let postCodeInput = $(this).val().trim();
        //if (postCodeInput.length === 6 && postCodeInput !== lastCheckedPin) {
        if (postCodeInput.length === 6) {
            $(this).blur();
            lastCheckedPin = postCodeInput; // store checked pin
            if ($("input[name='payment_method']:checked").length === 0) {
                $("#delivery_status").text("❌ Please select your Payment Method first").css("color", "red");
                $('#post_code').val('');
                return; 
            }
            $("#delivery_status").text("");
            checkDeliveryService(postCodeInput);
        }else{
            $("#delivery_status").text("");
            $("#cod_status").text("");
           // $("#shipping_amount").text("$0.00");
            // $("input[value='cod']").prop("checked", false);
            // $("input[value='paypal']").prop("checked", false);
            updateTotalPrice(0);
        }
    });

});
function checkDeliveryService(pin = null) {
   // var shippingAmount = 50;
    if(pin == null){
        pin = $('#post_code').val();
    }
    $('#delivery_status').text("Checking...").css("color", "blue");
    $(".preloader").show();
    var url = window.APP_URLS.checkserviceavailbilty;
    var selectedPaymentMethod = $("input[name='payment_method']:checked").val();
    $.ajax({
        url: url,  // your backend route
        method: "POST",
        data: {
            pincode: pin,
            _token: $('meta[name="csrf-token"]').attr('content'),
            paymentMethod:selectedPaymentMethod
        },
        success: function (response) {
            $(".preloader").hide();
            // OLD CODE
            // if (response.status === 200 && response.data.delivery_codes.length > 0) {
            //     let postal = response.data.delivery_codes[0].postal_code;
            //     console.log("Delivery codes found:", postal);
            //     // $("#country").val(postal.country_code);
            //      setTimeout(() => {
            //         $("#country").val((postal.country_code || '').toUpperCase()).trigger("change");
            //     }, 200);

            //     if (postal.pre_paid === "Y" || postal.cod === "Y") {
            //         $("#delivery_status").text("✔ Delivery Available")
            //             .css("color", "green");

            //         if (postal.cod === "Y") {

            //             $("#cod_status").text("COD Available").css("color", "green");
            //             $("input[value='cod']").prop("disabled", false);

            //         } else {

            //             $("#cod_status").text("COD Not Available").css("color", "red");
            //             $("input[value='cod']").prop("disabled", true);

            //             // If COD was selected before -> auto unselect and make PayPal default
            //             if ($("input[value='cod']").is(":checked")) {
            //                 $("input[value='cod']").prop("checked", false);
            //                 $("input[value='paypal']").prop("checked", true);
            //             }
            //         }
            //     } else {
            //         $("#delivery_status").text("❌ Delivery Not Available")
            //             .css("color", "red");
            //     }

            // } else {
            //     $("input[value='cod']").prop("disabled", true);
            //     $("#delivery_status").text("Invalid Pincode or No Service")
            //         .css("color", "red");
            //     console.log("No delivery codes found for this pincode.");
            // }
            
            // NEW CODE
            //if (response.status === 200 && response.data.data.available_courier_companies.length > 0) {
            if (response.status === 200 && response.data?.data?.available_courier_companies?.length > 0) {
                // $("#shipping_amount").text("$ " + shippingAmount);
                // $("#shipping_input").val(shippingAmount);
                // updateTotalPrice(shippingAmount);

                let postal = response.data.data.available_courier_companies[0].postcode;
                console.log("Delivery codes found:", postal);
                 setTimeout(() => {
                    $("#country").val((postal.country_code || '').toUpperCase()).trigger("change");
                }, 200);
                //$("#delivery_status").text("✔ Delivery Available").css("color", "green");
                if(selectedPaymentMethod == 'cod'){
                    $("#delivery_status").text("✔ Delivery Available with COD").css("color", "green");
                    $("input[value='cod']").prop("disabled", false);
                }
                if(selectedPaymentMethod == 'paypal' || selectedPaymentMethod == 'razorpay'){
                    $("#delivery_status").text("✔ Delivery Available with Prepaid").css("color", "green");
                        //$("input[value='cod']").prop("disabled", true);
                        // If COD was selected before -> auto unselect and make PayPal default
                        // if ($("input[value='cod']").is(":checked")) {
                        //     $("input[value='cod']").prop("checked", false);
                        //     $("input[value='paypal']").prop("checked", true);
                        // }
                }
            } else {
                //$("input[value='cod']").prop("disabled", true);
                $("#delivery_status").text("Invalid Pincode or No Service").css("color", "red");
                
                console.log("No delivery codes found for this pincode.");
            }
        },
        error: function () {
            $("#delivery_status").text("API Error").css("color", "red");
        }
    });
}

function checkshipingcharges(element) {
    $('#post_code').val('');
    $("#delivery_status").text("");
    return;
    var payment_method = element.value;
    var pincode = $('#post_code').val().trim();
    var shiping_url = window.APP_URLS.checkshipingcharges;
    if(pincode.length !== 6){
        alert("Please enter a valid 6-digit pincode.");
        return;
    }

    // Optional: show loader
    $("#shipping_amount").text("Calculating...");

    $.ajax({
        url: shiping_url,
        type: "POST",
        data: {
            pincode: pincode,
            payment_method: payment_method,
            weight: 500, // you can make this dynamic
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if(response.status === "success" && response.charges.length > 0){
                let shippingAmount = response.charges[0].total_amount ?? 0;
                console.log(shippingAmount);
                $("#shipping_amount").text("₹ " + shippingAmount);
                
                // (optional) Set hidden input in checkout form
                $("#shipping_input").val(shippingAmount);
                 updateTotalPrice(shippingAmount);
            } else {
                $("#shipping_amount").text("No rates available.");
            }
        },
        error: function() {
            $("#shipping_amount").text("Error fetching shipping charges.");
        }
    });

}

function updateTotalPrice(shippingAmount){
    let subtotal = parseFloat($("#order_total_price span").data("base")); 
    console.log("subtotal - " + subtotal);
    //let couponDiscount = parseFloat($(".coupon_price").data("price") || 0);
    //let finalTotal = (subtotal - couponDiscount) + shippingAmount;
    let finalTotal = subtotal + shippingAmount;
    $("#order_total_price span").text("₹" + finalTotal.toFixed(2));
}
