<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteGuestUser extends Model
{
 
    protected $table = "invite_guest_users"; 
     
     
    protected $fillable = [
        'sender_id',
        'email'
    ];

    
    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
}
