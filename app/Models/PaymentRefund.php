<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRefund extends Model
{
    protected $table = 'payment_refunds';

    protected $fillable = [
        'order_id',
        'payment_id',
        'razorpay_payment_id',
        'razorpay_refund_id',
        'refund_amount',
        'refund_status',
        'refund_response',
        'refund_reason',
        'refunded_at'
    ];

    public function payment()
    {
        return $this->belongsTo(PaymentOrders::class, 'payment_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}