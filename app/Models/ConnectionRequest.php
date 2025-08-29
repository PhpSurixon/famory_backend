<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ConnectionRequest extends Model
{
 

    
    protected $table = "connection_requests"; 
     
     
    protected $fillable = [
        'sender_id',
        'user_id',
        'group_id',
        'is_verify',
        'msg',
        'status',
        'sender_delete',
    ];


    protected $hidden = [
        'updated_at',
        
    ];
    
//  public function getCreatedAtAttribute($value)
// {
//     $createdAt = Carbon::parse($value);
//     return $createdAt->format('Y-m-d h:i:s A');
// }
    // public function getCreatedAtAttribute($value)
    // {
    //     return Carbon::parse($value)->format('m/d/Y');
    // }

    public function user() {
        return $this->belongsTo(User::class,'sender_id','id')->select('id','first_name','last_name','image');
    }
    
    public function group() {
        return $this->belongsTo(UserGroup::class,'group_id','id')->select('id','name');
    }
    
    
    public function sender() {
        return $this->belongsTo(User::class,'user_id','id')->select('id','first_name','last_name','image');
    }
    

    
}
