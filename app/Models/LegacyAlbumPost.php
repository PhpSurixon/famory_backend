<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyAlbumPost extends Model
{
    protected $table = "legacy_album_posts"; 

    protected $fillable = [
        'legacy_album_id',
        'post_id',
        'user_id',
    ];

    public function lagecyalbum()
    {
        return $this->belongsTo(Album::class, 'legacy_album_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
