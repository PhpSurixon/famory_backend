<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailOrderReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $guestName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($datas)
    {
        $this->data = $datas;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.SendMailOrderReceived')
        ->with([
            'data' => $this->data,
        ])->subject("Order Received");
    }
}