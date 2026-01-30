<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagUser extends Model
{
    protected $table = 'tag_users';

    protected $fillable = [
        'tag_id',
        'user_id',
        'role',
        'approval_status',
        'invited_by',
    ];

    public function tags()
    {
        return $this->belongsTo(FamilyTagId::class,'tag_id')->wher('is_deleted',0);
    }

    /**
     * Relationship: The user (collaborator or viewer)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inviter()
    {
      return $this->belongsTo(User::class, 'invited_by');
    }

    public function tagOwner()
    {
        return $this->hasOneThrough(
            User::class,          // Final model
            FamilyTagId::class,   // Intermediate model
            'id',                 // FamilyTagId.id
            'id',                 // User.id
            'tag_id',             // TagUser.tag_id
            'created_user_id'     // FamilyTagId.created_user_id
        )->select('users.id','users.first_name','users.last_name','users.email','users.username','users.image');
    }
}
