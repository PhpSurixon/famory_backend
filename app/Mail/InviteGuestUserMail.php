<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteGuestUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $guestName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token,$guestName)
    {
        $this->token = $token;
        $this->guestName = $guestName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fullName = $this->token->first_name . ' ' . $this->token->last_name;
        return $this->markdown('Email.InviteGuestUser')
        ->with([
            'token' => $this->token,
            'guestName' => $this->guestName,
        ])->subject("{$fullName} has invited you to join Famory - Let's keep the memories alive!");
    }
}