<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $refundId;
    public $cancelReason;

    public function __construct($order, ?string $refundId = null, ?string $cancelReason = null)
    {
        $this->order        = $order;
        $this->refundId     = $refundId;
        $this->cancelReason = $cancelReason;
    }

    public function build()
    {
        return $this->subject('Your Order Has Been Cancelled - ' . $this->order->unique_order_id)
            ->view('Email.OrderCancelledMail')
            ->with([
                'order'        => $this->order,
                'refundId'     => $this->refundId,
                'cancelReason' => $this->cancelReason,
            ]);
    }
}
