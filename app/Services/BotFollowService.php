<?php

namespace App\Services;

use App\Models\User;
use App\Models\Follow;
use App\Traits\OneSignalTrait;

class BotFollowService
{
    use OneSignalTrait;

    public function followRandomUser()
    {
        if (!botsEnabled()) return;

        // 🎯 1 random bot
        $bot = User::where('is_bot', 1)
            ->where('role_id', 2)
            ->where('ban_user', 0)
            ->whereNull('deleted_at')
            ->inRandomOrder()
            ->first();

        if (!$bot) return;

        // 🎯 1 random real user
        $user = User::where('is_bot', 0)
            ->where('id', '!=', $bot->id)
            ->where('role_id', 2)
            ->where('ban_user', 0)
            ->where('is_private', 0)
            ->whereNull('deleted_at')
            ->inRandomOrder()
            ->first();

        if (!$user) return;

        // 🚫 Already following?
        $exists = Follow::where([
            'follower_id'  => $bot->id,
            'following_id' => $user->id
        ])->exists();

        if ($exists) return;

        Follow::create([
            'follower_id'  => $bot->id,
            'following_id' => $user->id,
            'status'       => 'approved'
        ]);

        // 🔔 Notification (to real user)
        $this->notifyMessage(
            $bot,        // sender (bot)
            $user->id,   // receiver
            $bot,        // context (sender profile)
            'follow'
        );
    }
}
