<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagCollaborator extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tag_collaborators';

    protected $fillable = [
        'family_tag_id',
        'user_id',
        'invited_by',
        'request_message',
        'request_type',
        'status',
        'permissions_level',
        'invited_at',
        'email',
        'accepted_at',
        'avatar', // Added avatar field
    ];

    /**
     * Accessor to return the correct avatar URL.
     */
    public function getAvatarAttribute($value)
    {
        $newBaseUrl = config('services.s3.cdn_url');
        $oldBaseUrl = 'https://famorys3.s3.amazonaws.com';

        if (strpos($value, '/') === 0) {
            return $newBaseUrl . $value;
        }

        if (strpos($value, $oldBaseUrl) !== false) {
            return str_replace($oldBaseUrl, $newBaseUrl, $value);
        }

        return $value ?? '';
    }
    
    /**
     * Get the user who is the collaborator.
     */
    public function collaborator()
    {
        return $this->belongsTo(User::class, 'collaborator_user_id');
    }

    /**
     * Get the user who invited the collaborator.
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    /**
     * Get the family tag associated with the collaborator.
     */
    public function familyTag()
    {
        return $this->belongsTo(FamilyTagId::class, 'tag_id');
    }

    public function invitedByUser()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
