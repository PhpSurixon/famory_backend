<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
 

    
    protected $table = "contacts"; 
     
     
    protected $fillable = [
        'name',
        'user_id',
        'email',
        'phone',
        'message'
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    


     

    
}
