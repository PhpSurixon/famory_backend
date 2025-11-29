<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PostController;

class RunReoccurringPost extends Command
{
    protected $signature = 'cron:reoccurring-post';
    protected $description = 'Run Reoccurring Post Cron Job';

    public function handle()
    {
        // Resolve controller correctly so Laravel injects dependencies
        $controller = app(PostController::class);

        // Call your method
        $controller->runCronJobPost(request());

        return Command::SUCCESS;
    }
}
