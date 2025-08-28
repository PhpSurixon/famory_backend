<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulingPost extends Model
{
 

    
    protected $table = "scheduling_posts"; 
     
     
    protected $fillable = [
        'post_id',
        'schedule_type',
        'schedule_time',
        'schedule_date',
        'reoccurring_type',
        'reoccurring_time',
        'is_post',
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
//   public function user() {
//         return $this->belongsTo(User::class,'member_id','id')->select('id','first_name','last_name','image');
//     }

    
}
