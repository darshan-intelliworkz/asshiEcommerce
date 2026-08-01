<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    protected $fillable=['user_id','order_number' , 'shiping_charges','sub_total','quantity','delivery_charge','status','total_amount','first_name','last_name','country','state','city','post_code','address1','address2','phone','email','payment_method','payment_status','shipping_id','coupon', 'total_gst_amount', 'delivered_at'];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function cart_info(){
        return $this->hasMany('App\Models\Cart','order_id','id');
    }
    public static function getAllOrder($id){
        return Order::with('cart_info')->find($id);
    }
    public static function countActiveOrder(){
        $data=Order::count();
        if($data){
            return $data;
        } 
        return 0;
    }
    public function cart(){
        return $this->hasMany(Cart::class);
    }
 
    public function shipping(){
        return $this->belongsTo(Shipping::class,'shipping_id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function payments()
    {
        return $this->hasMany(PaymentOrders::class, 'order_id');
    }

    public function refunds()
    {
        return $this->hasMany(PaymentRefund::class, 'order_id');
    }

    public function returnRequests()
    {
        return $this->hasMany(OrderReturnRequest::class, 'order_id');
    }

    protected static function booted()
    {
        static::updated(function ($model) {
            if ($model->wasChanged('status')) {
                Log::info('Status updated', [
                    'model'      => class_basename($model),
                    'id'         => $model->id,
                    'old_status' => $model->getOriginal('status'),
                    'new_status' => $model->status,
                    'user_id'    => auth()->id(),
                ]);
            }
        });
    }

}
