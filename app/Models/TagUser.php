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
    ];

    public function tags()
    {
        return $this->belongsTo(FamilyTagId::class);
    }

    /**
     * Relationship: The user (collaborator or viewer)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
