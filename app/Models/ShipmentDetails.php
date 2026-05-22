<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentDetails extends Model
{
    use SoftDeletes;
    protected $table='shipment_details';
    protected $fillable=[
        'order_id',
        'order_number',
        'shipment_awb',
        'pickup_id',
        'label_pdf',
        'shipment_status',
        'shipment_response',
        'scheduled_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'shipment_id',
        'shipment_order_id',
        'pickup_request_response',
        'manifest_url',
        'invoice_url'

    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
