<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscribedPartner extends Model
{
    protected $table = "subscribed_partners"; 
     
     
    protected $fillable = [
        'partner_id', 	
        'user_id', 	
        'payment_indent_id',
        'charge_id',
        'source',
        'source_type',
        'amount',
        'type',
        'subscription_type',
        'cancel_status'
        
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        'cancel_at'
    ];
    
}