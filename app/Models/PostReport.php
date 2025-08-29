<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PostReport extends Model
{

    protected $fillable = [
        'user_id',
        'post_id',
        'email',
        'message',
        'phone',
    ];
    
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

}

