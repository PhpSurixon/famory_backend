<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
 
    protected $table = "Subscriptions"; 
     
     
    protected $fillable = [
        'user_id',
        'subscription',
        'receipt',
        'platform',
        'expiry_date',
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    
}
