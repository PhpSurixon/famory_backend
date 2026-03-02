<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected $commands = [
        \App\Console\Commands\RunReoccurringPost::class,
        \App\Console\Commands\BotEngagementCron::class,
        \App\Console\Commands\SendDeathUserNotification::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run every minute (you can change to hourly/daily)
        $schedule->command('cron:reoccurring-post')->everyMinute();
        $schedule->command('bots:engage')->everyMinute()->withoutOverlapping();
        $schedule->command('send:user-notification')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
