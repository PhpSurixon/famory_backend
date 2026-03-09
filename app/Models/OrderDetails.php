<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;
    protected $table = 'order_details';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'product_id',
        'cart_id',
        'buy_quantity',
        'product_unit_price',
        'product_json'
    ];
}
