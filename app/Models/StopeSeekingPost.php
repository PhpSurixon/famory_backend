<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StopeSeekingPost extends Model
{
 
    protected $table = "stop_seeking_posts"; 
     
     
    protected $fillable = [
        'user_id',
        'post_id'
    ];

    
    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
}
