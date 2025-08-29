<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
 
    protected $table = "likes"; 
     
     
    protected $fillable = [
        'user_id',
        'post_id',
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
    public function user() {
        return $this->belongsTo(User::class,'user_id','id')->select('id','first_name','last_name','image');
    }

    
    // public function scheduling_post() {
    //     return $this->hasOne(SchedulingPost::class,'post_id','id');
    // }
}
