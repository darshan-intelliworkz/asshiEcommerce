<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentOrders extends Model
{
    protected $table = 'order_payments';

    protected $fillable = [
        'order_id',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'amount',
        'transaction_id',
        'payment_status',
        'payment_response'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class, 'payment_id');
    }
}