<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionSetting extends Model
{
    protected $table = "subscription_settinges"; 
     
     
    protected $fillable = [
        'plans',
        'benefits',
        'plan_id_ios',
        'plan_id_android',
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    


     

    
}
