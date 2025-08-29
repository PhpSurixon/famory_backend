<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockUser extends Model {


    protected $dates = ['deleted_at'];
    
    protected $table = "block_users"; 
     protected $fillable = [
        'user_id',
        'marked_user_id',
        'is_live',
        'block',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    
    // public function user() {
    //     return $this->belongsTo(User::class);
    // }
      public function blockedUser()
    {
        return $this->belongsTo(User::class, 'marked_user_id');
    }
}
