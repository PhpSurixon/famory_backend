<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailOrderSend extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $guestName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($datas,$shipTrackingId)
    {
        $this->data = $datas;
        $this->shipTrackingId = $shipTrackingId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.SendMailOrderSend')
        ->with([
            'data' => $this->data,
            'shipTrackingId' => $this->shipTrackingId,
        ])->subject("Order Send");
    }
}