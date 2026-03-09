<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    use HasFactory;
    protected $table = 'order_payments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'payment_intent_id',
        'stripe_transaction_id',
        'amount',
        'payment_status'
    ];
}
