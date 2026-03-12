<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Http\Interfaces\OrderStatusInterface;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class CancelExpiredOrders extends Command
{
    /**
     * Orders that remain Pending for longer than this (in hours) will be cancelled.
     */
    const EXPIRY_HOURS = 24;

    protected $signature   = 'orders:cancel-expired';
    protected $description = 'Cancel Pending orders where the user never completed payment after '.self::EXPIRY_HOURS.' hours';

    public function handle(): void
    {
        $cutoff = now()->subHours(self::EXPIRY_HOURS);

        /*
        |---------------------------------------
        | Find expired Pending orders
        |---------------------------------------
        */

        $expiredOrders = Order::with('orderDetail')
            ->where('last_status_id', OrderStatusInterface::Pending)
            ->where('order_datetime', '<', $cutoff)
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No expired orders found.');
            return;
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $cancelled = 0;

        foreach ($expiredOrders as $order) {

            try {

                /*
                |---------------------------------------
                | Cancel Stripe PaymentIntent if exists
                |---------------------------------------
                */

                if ($order->payment_intent_id) {
                    try {
                        $pi = PaymentIntent::retrieve($order->payment_intent_id);

                        // Only cancellable if not already succeeded/canceled
                        if (!in_array($pi->status, ['succeeded', 'canceled'])) {
                            $pi->cancel();
                        }

                    } catch (\Exception $e) {
                        // Stripe error — log and continue; still cancel order locally
                        Log::warning("Stripe cancel failed for order {$order->id}: ".$e->getMessage());
                    }
                }

                /*
                |---------------------------------------
                | Mark payment record as failed
                |---------------------------------------
                */

                OrderPayment::where('order_id', $order->id)
                    ->where('payment_status', 0) // pending only
                    ->update(['payment_status' => 2]);

                /*
                |---------------------------------------
                | Update order status to Failed
                |---------------------------------------
                */

                $order->update(['last_status_id' => OrderStatusInterface::Failed]);

                $cancelled++;

                Log::info("Expired order {$order->id} ({$order->unique_order_id}) cancelled by scheduler.");

            } catch (\Exception $e) {
                Log::error("CancelExpiredOrders failed for order {$order->id}: ".$e->getMessage());
            }
        }

        $this->info("Cancelled {$cancelled} expired order(s).");
    }
}
