<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\OneSignalTrait;

class SendCustomNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use OneSignalTrait;

    protected $sender;
    protected $userIds;
    protected $title;
    protected $message;

    public function __construct($sender, $userIds, $title, $message)
    {
        $this->sender  = $sender;
        $this->userIds = $userIds;
        $this->title   = $title;
        $this->message = $message;
    }

    public function handle()
    {
        foreach ($this->userIds as $userId) {
            $this->notifyMessage(
                $this->sender,
                $userId,
                null,
                'custom_notification',
                null,
                null,
                $this->title,
                $this->message
            );
        }
    }
}
