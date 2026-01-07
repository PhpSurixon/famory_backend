<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BotLikeService;
use App\Services\BotFollowService;
use App\Services\BotCommentService;

class BotEngagementCron extends Command
{
    protected $signature = 'bots:engage';
    protected $description = 'Bots like posts and follow users every minute';

    protected BotLikeService $botLikeService;
    protected BotFollowService $botFollowService;
    protected BotCommentService $botCommentService;

    // ✅ Dependency Injection
    public function __construct(
        BotLikeService $botLikeService,
        BotFollowService $botFollowService,
        BotCommentService $botCommentService
    ) {
        parent::__construct();
        $this->botLikeService   = $botLikeService;
        $this->botFollowService = $botFollowService;
        $this->botCommentService = $botCommentService;

    }

    public function handle()
    {
        // 🔒 ADMIN TOGGLE CHECK
        if (!botsEnabled()) {
            $this->info('Bots are disabled');
            return;
        }

        // 🔥 1 random bot like
        $this->botLikeService->likeRandomPost();

        // 🔥 1 random bot follow
        $this->botFollowService->followRandomUser();

        $this->botCommentService->commentRandomPost();

        // if (rand(1, 100) <= 50){ // 30% chance
        //     $this->botCommentService->commentRandomPost();
        // }

        $this->info('Bot engagement executed successfully');
    }
}
