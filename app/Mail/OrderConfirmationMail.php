<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $invoiceFile;

    public function __construct($order, $invoiceFile)
    {
        $this->order = $order;
        $this->invoiceFile = $invoiceFile;
    }

    public function build()
    {
        $order = $this->order;
        $order->address_data = json_decode($order->address_data, true);
        return $this->subject(subject: 'Order Confirmation - '.$this->order->unique_order_id)
            ->view('Email.order_confirmation')
            ->attach($this->invoiceFile, [
                'as' => 'invoice_'.$order->unique_order_id.'.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
