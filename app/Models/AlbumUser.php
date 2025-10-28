<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlbumUser extends Model
{
    protected $table = 'album_users';

    protected $fillable = [
        'album_id',
        'user_id',
        'role',
    ];

    // Optional: hide timestamps in responses
    // protected $hidden = [
    //     'created_at',
    //     'updated_at',
    // ];

    /**
     * Relationship: The album this record belongs to
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Relationship: The user (collaborator or viewer)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper: Check if the user is a collaborator
     */
    public function isCollaborator()
    {
        return $this->role === 'collaborator';
    }

    /**
     * Helper: Check if the user is a viewer
     */
    public function isViewer()
    {
        return $this->role === 'viewer';
    }
}
