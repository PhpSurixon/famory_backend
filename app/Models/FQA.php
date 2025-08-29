<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FQA extends Model
{
 
    protected $table = "fqas"; 
     
     
    protected $fillable = [
       'question','answer'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];

}
