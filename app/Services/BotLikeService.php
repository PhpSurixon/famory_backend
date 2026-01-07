<?php
namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\Like;
use App\Traits\OneSignalTrait;

class BotLikeService
{
    use OneSignalTrait;

    public function likeRandomPost()
    {
        if (!botsEnabled()) return;

        $bot = User::where('is_bot', 1)
            ->where('role_id', 2)
            ->where('ban_user', 0)
            ->whereNull('deleted_at')
            ->inRandomOrder()
            ->first();

        if (!$bot) return;

        $post = Post::where('user_id', '!=', $bot->id)
            ->where('post_type', 'public')
            ->inRandomOrder()
            ->first();

        if (!$post) return;

        $exists = Like::where([
            'post_id' => $post->id,
            'user_id' => $bot->id
        ])->exists();

        if ($exists) return;

        Like::create([
            'post_id' => $post->id,
            'user_id' => $bot->id
        ]);

        // ✅ Notification
        $this->notifyMessage($bot, $post->user_id, $post, 'like');
    }
}
