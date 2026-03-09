<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'unique_order_id',
        'invoice_no',
        'user_id',
        'user_address_id',
        'address_data',
        'order_datetime',
        'last_status_id',
        'payment_mode',
        'subtotal_amount',
        'shipping_amount',
        'payable_amount',
        'payment_intent_id',
        'waybill'
    ];

    public function orderDetail()
    {
       return $this->hasMany(OrderDetails::class, 'order_id', 'id');
    } 

}