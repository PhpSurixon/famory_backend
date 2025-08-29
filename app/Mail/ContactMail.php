<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;
    public $username;
    public $email;
    public $phone;
    public $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username,$email, $phone,$message)
    {
        $this->username = $username;
        $this->email = $email;
        $this->phone = $phone;
        $this->message=$message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('Email.contactPageData')
        ->with([
            'username'=>$this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
        ]);
    }
}