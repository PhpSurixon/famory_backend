<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AlbumPost extends Model
{

    protected $fillable = [
        'album_id',
        'post_id',
        'user_id',
    ];
    
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

