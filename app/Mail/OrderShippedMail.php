<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $waybill;
    public $trackingUrl;

    public function __construct($order, string $waybill, ?string $trackingUrl = null)
    {
        $this->order       = $order;
        $this->waybill     = $waybill;
        $this->trackingUrl = $trackingUrl;
    }

    public function build()
    {
        return $this->subject('Your Order Has Been Shipped - ' . $this->order->unique_order_id)
            ->view('Email.OrderShippedMail')
            ->with([
                'order'       => $this->order,
                'waybill'     => $this->waybill,
                'trackingUrl' => $this->trackingUrl,
            ]);
    }
}
