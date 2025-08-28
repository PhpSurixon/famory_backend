<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutorial extends Model
{
 
    protected $table = "tutorials"; 
     
     
    protected $fillable = [
       'image','title','details'
    ];

    protected $hidden = [
        'image',
        'created_at',
        'updated_at',
        
    ];

}
