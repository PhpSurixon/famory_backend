<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'comment',
        'parent_id',
    ];
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     // Recursive relationship: A comment can have many replies
     public function replies()
     {
         return $this->hasMany(Comment::class, 'parent_id');
     }
 
     // Relationship: A reply belongs to a parent comment
     public function parent()
     {
         return $this->belongsTo(Comment::class, 'parent_id');
     }

     public function likes()
     {
         return $this->hasMany(Like::class);
     }
 
     // A comment can be liked by many users (many-to-many)
     public function usersWhoLiked()
     {
         return $this->belongsToMany(User::class, 'commentlikes');
     }
}
