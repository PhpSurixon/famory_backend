<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BotLikeService;
use App\Services\BotFollowService;

class BotEngagementCron extends Command
{
    protected $signature = 'bots:engage';
    protected $description = 'Bots like posts and follow users every minute';

    public function handle()
    {
        // 🔒 ADMIN TOGGLE CHECK
        if (!botsEnabled()) {
            $this->info('Bots are disabled');
            return;
        }

        // 🔥 Run bot like
        BotLikeService::likeRandomPost();

        // 🔥 Run bot follow
        BotFollowService::followRandomUser();

        $this->info('Bot engagement executed');
    }
}
