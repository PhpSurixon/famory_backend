<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsPrice extends Model
{
 
    protected $table = "ads_prices"; 
     
     
    protected $fillable = [
        'day',
        'price',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    

}
