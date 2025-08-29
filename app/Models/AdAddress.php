<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdAddress extends Model
{
 
    protected $table = "ad_address"; 
     
     
    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'house_number',
        'road_name',
        'state',
        'zip_code'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    

}
