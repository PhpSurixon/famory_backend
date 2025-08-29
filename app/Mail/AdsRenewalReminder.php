<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdsRenewalReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $guestName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($adsdata,$user)
    {
        $this->adsdata = $adsdata;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.AdsRenewalReminder')
        ->with([
            'adsdata' => $this->adsdata,
            'user' => $this->user,
        ])->subject("Renewal Reminder");
    }
}