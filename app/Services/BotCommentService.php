<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Traits\OneSignalTrait;
use Illuminate\Support\Str;
class BotCommentService
{
    use OneSignalTrait;

    public function commentRandomPost()
    {
        // 🔒 Admin toggle
        if (!botsEnabled()) return;

        // 🎯 Random bot
        $bot = User::where('is_bot', 1)
                    ->where('role_id', 2)
                    ->where('ban_user', 0)
                    ->whereNull('deleted_at')
                    ->inRandomOrder()
                    ->first();

        if (!$bot) return;

        // 🎯 Random public post (not bot's own)
        $post = Post::where('post_type', 'public')
                    ->where('user_id', '!=', $bot->id)
                    ->inRandomOrder()
                    ->first();

        if (!$post) return;

        // 🚫 Rate limit (bot already commented recently)
        $alreadyCommented = Comment::where('user_id', $bot->id)
                                    ->where('post_id', $post->id)
                                    ->where('created_at', '>=', now()->subHours(6))
                                    ->exists();

        if ($alreadyCommented) return;

        // 🧠 Generate comment
        $commentText = $this->generateComment();

        // 💬 Save comment
        $comment = Comment::create([
            'post_id'   => $post->id,
            'user_id'   => $bot->id,
            'parent_id' => null,
            'comment'   => $commentText,
        ]);

        // 🔔 Notify post owner (bot ≠ owner)
        if ($post->user_id !== $bot->id) 
        {
            $preview = Str::limit(strip_tags($commentText), 20);                   
            $this->notifyMessage($bot, $post->user_id, $post, 'comment', null, null,null,$preview);
        }
    }

    private function generateComment(): string
    {
        $generic = [
            "🙂",
            "😊",
            "👍",
            "👌",
            "🤔",
            "👀",
            "😌",
            "✨",
            "🌟",
            "💫",
            "🤝",
            "🫶",
            "❤️",
            "💙",
            "💚",
            "💛",
            "💜",
            "🧡",
            "🖤",
            "🤍",
            "🔥",
            "💯",
            "👏",
            "🙌"
        ];

        $contextual = [
            "🙂👍",
            "👍🙂",
            "🤔🙂",
            "👀🙂",
            "✨🙂",
            "❤️🙂",
            "💙🙂",
            "👌🙂",
            "💯🔥",
            "👏🙂",
            "🙌✨",
            "😊👍",
            "🌟🙂",
            "💫🙂",
            "🤝🙂"
        ];

        $questions = [
            "Nice",
            "Nice 🙂",
            "Yo",
            "Yo 🤔",
            "Valid",
            "Valid 👌",
            "Great",
            "Great ✨",
            "Excellent",
            "Excellent 🙂",
            "Solid",
            "Solid 💯",
            "Cool",
            "Cool 🙂"
        ];
        
        $genericArr = array_rand(array_flip($generic), 2);
        $contextualArr = array_rand(array_flip($contextual), 2);
        $questionsArr = (array) array_rand(array_flip($questions), 1); // cast to array

        $mix = array_merge($genericArr, $contextualArr, $questionsArr);

        return $mix[array_rand($mix)];
    }
}
