<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserLiveStatus extends Model
{

    protected $fillable = [
       'user_id',
       'is_alive',
       'deceased_by',
       'alive_by',
    ];
    
    
    protected $hidden = [
        // 'created_at',
        'updated_at',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
    
    public function deceasedBy(){
        return $this->belongsTo(User::class,'deceased_by','id');
    }
}

