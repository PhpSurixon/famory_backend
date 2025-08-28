<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailreset extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $first_name;
    public $last_name;

    public function __construct($token, $email, $first_name, $last_name)
    {
        $this->token = $token;
        $this->email = $email;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
    }

    public function build()
    {
        return $this->markdown('Email.passwordReset')
                    ->with([
                        'token' => $this->token,
                        'email' => $this->email,
                        'first_name' => $this->first_name,
                        'last_name' => $this->last_name
                    ]);
    }
}
