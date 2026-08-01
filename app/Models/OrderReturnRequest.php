<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturnRequest extends Model
{
    protected $table = 'order_return_requests';

    protected $fillable = [
        'order_id',
        'cart_id',
        'return_type',
        'reason',
        'customer_comment',
        'customer_upi_id',
        'admin_comment',
        'images',
        'status',
        'shiprocket_return_order_id',
        'shiprocket_shipment_id',
        'awb_code',
        'courier_name',
        'pickup_token_number',
        'pickup_scheduled_date',
        'pickup_status',
        'current_tracking_status',
        'tracking_data',
        'refund_status',
        'refund_amount',
        'refund_id',
        'exchange_order_id',
        'exchange_shipment_id',
        'create_return_payload',
        'create_return_response',
        'exchange_create_payload',
        'exchange_create_response',
        'awb_payload',
        'awb_response',
        'pickup_payload',
        'pickup_response',
        'tracking_payload',
        'refund_payload',
        'error_response',
        'approved_at',
        'rejected_at',
        'pickup_completed_at',
        'refunded_at',
        'exchange_approved_at',
    ];

    protected $casts = [

       

        'images' => 'array',

        'create_return_payload' => 'array',

        'create_return_response' => 'array',

        'exchange_create_payload' => 'array',

        'exchange_create_response' => 'array',

        'awb_payload' => 'array',

        'awb_response' => 'array',

        'pickup_payload' => 'array',

        'pickup_response' => 'array',

        'tracking_payload' => 'array',

        'refund_payload' => 'array',

        'error_response' => 'array',

        'pickup_scheduled_date' => 'datetime',

        'approved_at' => 'datetime',

        'rejected_at' => 'datetime',

        'pickup_completed_at' => 'datetime',

        'refunded_at' => 'datetime',

        'exchange_approved_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function exchangeOrder()
    {
        return $this->belongsTo(
            Order::class,
            'exchange_order_id'
        );
    }
}
