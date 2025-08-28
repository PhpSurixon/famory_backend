<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
 

    
    protected $table = "about_us"; 
     
     
    protected $fillable = [
        'title',
        'image',
        'details',
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    


     

    
}
