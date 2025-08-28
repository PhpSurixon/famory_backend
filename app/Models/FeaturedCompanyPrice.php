<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedCompanyPrice extends Model
{
 
    protected $table = "featured_company_price"; 
     
     
    protected $fillable = [
        'month',
        'price',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    

}
