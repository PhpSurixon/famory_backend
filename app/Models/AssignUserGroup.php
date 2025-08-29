<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignUserGroup extends Model
{
 

    
    protected $table = "assgin_user_groups"; 
     
     
    protected $fillable = [
        'user_id',
        'user_group_id',
        'is_add',
        'is_notify',
    ];


    protected $hidden = [
        'is_add',
        'created_at',
        'updated_at',
        
    ];
    
    public function group_name() {
        return $this->belongsTo(UserGroup::class, 'user_group_id', 'id');
    }

     

    
}
