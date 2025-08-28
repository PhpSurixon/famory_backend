<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowerUnfollwer extends Model
{
 
    protected $table = "follow_unfollow"; 
     
     
    protected $fillable = [
        'user_id',
        'following_id',
        'status',
    ];


    protected $hidden = [
         'status',
        'created_at',
        'updated_at',
        
    ];
    
    public function user() {
        return $this->belongsTo(User::class,'following_id','id')->select('id','first_name','last_name','image');
    }

    
    // public function scheduling_post() {
    //     return $this->hasOne(SchedulingPost::class,'post_id','id');
    // }
}
