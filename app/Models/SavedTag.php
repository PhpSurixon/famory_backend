<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedTag extends Model
{
    use HasFactory;

    protected $table = 'saved_tags';

    protected $fillable = [
        'family_tag_id',
        'user_id',
        'is_removed',
    ];

    /**
     * Relationship with FamilyTagId
     */
    public function familyTag()
    {
        return $this->belongsTo(FamilyTagId::class, 'family_tag_id', 'family_tag_id');
    }

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
}
