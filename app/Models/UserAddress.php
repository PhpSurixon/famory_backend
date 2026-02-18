<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = "user_addresses"; 
     
     
    protected $fillable = [
        'user_id',
        'name',
        'phone_number',
        'house_number',
        'road_name',
        'state',
        'zip_code',
        'is_default'
    ];
}
