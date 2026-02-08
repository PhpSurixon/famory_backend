<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedTag extends Model
{
    use HasFactory;

    protected $table = 'saved_tags';

    protected $fillable = [
        'tag_id',
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
    public function tagData()
    {
        return $this->belongsTo(FamilyTagId::class, 'tag_id', 'id')
                ->where('is_deleted', 0);
    }

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
}
