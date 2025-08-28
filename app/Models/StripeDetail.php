<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StripeDetail extends Model
{

    protected $fillable = [
        "user_id",
        "stripe_customer_id",
        "card_number"
    ];
    
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
   
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
