<?php

namespace App\Jobs;

use App\Services\MailchimpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AddMailchimpSubscriberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    protected $email;
    protected $name;

    public function __construct($email, $name = '')
    {
        $this->email = $email;
        $this->name  = $name;
    }

    public function handle(MailchimpService $mailchimp)
    {
        $result = $mailchimp->addSubscriber($this->email, $this->name);

        if (isset($result['status']) && $result['status'] === false) {
            Log::error('Mailchimp subscribe failed', [
                'email'  => $this->email,
                'detail' => $result['message'] ?? null,
                'errors' => $result['errors'] ?? [],
            ]);
        }
    }
}
