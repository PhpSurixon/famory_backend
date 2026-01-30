<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagsPurchaseHistory extends Model
{
    protected $table = "tags_purchase_histories"; 
    protected $fillable = [
        'user_id',
        'tag_count',
        'package_name',
        'amount',
        'date',
        'status',
        'payment_id',
    ];
}
