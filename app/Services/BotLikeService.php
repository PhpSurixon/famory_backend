<?php
namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\Like;

class BotLikeService
{
    public static function likeRandomPost()
    {
        if (!botsEnabled()) return;

        // 🎯 1 random bot
        $bot = User::where('is_bot', 1)
                    ->where('role_id',2)
                    ->where('ban_user',0)
                    ->whereNull('deleted_at')
                    ->inRandomOrder()
                    ->first();
        if (!$bot) return;

        // 🎯 1 random post (not bot post optional)
        $post = Post::where('user_id', '!=', $bot->id)
                    ->where('post_type','public')
                    ->inRandomOrder()
                    ->first();

        if (!$post) return;

        // 🚫 Already liked?
        $exists = Like::where([
            'post_id' => $post->id,
            'user_id' => $bot->id
        ])->exists();

        if ($exists) return;

        Like::create([
            'post_id' => $post->id,
            'user_id' => $bot->id
        ]);
    }
}
