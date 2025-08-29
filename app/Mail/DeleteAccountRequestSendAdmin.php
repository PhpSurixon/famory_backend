<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeleteAccountRequestSendAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $reason;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $reason)
    {
        $this->name = $name;
        $this->email = $email;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.DeleteAccountRequestSendAdmin')
        ->with([
            'name' => $this->name,
            'email' => $this->email,
            'reason' => $this->reason
        ]);
    }
}