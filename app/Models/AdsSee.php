<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsSee extends Model
{
 
    protected $table = "ads_sees"; 
     
     
    protected $fillable = [
        'ads_id',
        'view',
        'click_to_open',
        'click_to_website',
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
}
