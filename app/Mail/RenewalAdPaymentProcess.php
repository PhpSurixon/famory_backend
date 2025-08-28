<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RenewalAdPaymentProcess extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $guestName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($adsdata,$user,$payment)
    {
        $this->adsdata = $adsdata;
        $this->user = $user;
        $this->payment = $payment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.RenewalAdPaymentProcess')
        ->with([
            'adsdata' => $this->adsdata,
            'user' => $this->user,
            'payment' => $this->payment,
        ])->subject("Payment Processed");
    }
}