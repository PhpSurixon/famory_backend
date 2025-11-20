<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyAlbum extends Model
{
    protected $table = "legacy_albums"; 
    protected $fillable = [
        'user_id',
        'shared_with_id',
        'title',
        'conver_image',
        'type'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sharedWith()
    {
        return $this->belongsTo(User::class, 'shared_with_id');
    }

    public function posts()
    {
        return $this->hasMany(LegacyAlbumPost::class, 'legacy_album_id');
    }
}
