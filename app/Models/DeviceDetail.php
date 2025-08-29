<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceDetail extends Model
{
 
    protected $table = "device_details"; 
     
     
    protected $fillable = [
        'user_id',
        'device_token',
        'platform',
        'app_version',
        'uuid',
        'is_user_loggedin',
        'time_zone',
        'is_prod',
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
}
