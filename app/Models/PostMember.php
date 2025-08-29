<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMember extends Model
{
 

    
    protected $table = "post_members"; 
     
     
    protected $fillable = [
       'post_id',
       'member_id',
       'post_by'
    ];

   
    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
    public function user() {
        return $this->belongsTo(User::class,'user_id','id')->select('id','first_name','last_name','image');
    }

    
    public function scheduling_post() {
        return $this->hasOne(SchedulingPost::class,'post_id','id');
    }
}
