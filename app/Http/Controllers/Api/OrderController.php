<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Carts;
use App\Models\Product;
use App\Models\UserAddress;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Interfaces\OrderStatusInterface;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Create Order (DB) + Stripe PaymentIntent (returns client_secret)
     * - Does NOT finalize order (stock reduction / cart removal)
     */
    public function createOrderStripeIntent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'address_id' => 'required|exists:user_addresses,id',
                'cart_id'    => 'nullable' // buy_now
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>'failed','message'=>$validator->errors()->first()], 400);
            }

            $authUser = Auth::user();
            $userId = $authUser->id;

            // validate address
            $user_address = UserAddress::select('id','user_id','name','phone_number','house_number','road_name','state','zip_code')
                ->where('id', $request->address_id)
                ->where('user_id', $userId)
                ->first();

            if (!$user_address) {
                return response()->json(['status'=>'failed','message'=>'Address not found'], 400);
            }

            // get cart items
            if ($request->filled('cart_id')) {
                $cartItems = Carts::with('product')
                    ->where('id', $request->cart_id)
                    ->where('user_id', $userId)
                    ->get();
                $cart_type = 'buy_now';
            } else {
                $cartItems = Carts::with('product')
                    ->where('user_id', $userId)
                    ->get();
                $cart_type = 'cart';
            }

            if ($cartItems->isEmpty()) {
                return response()->json(['status'=>'failed','message'=>'Cart empty'], 400);
            }

            // validate items & compute subtotal
            $subtotal = 0;
            foreach ($cartItems as $item) {
                if (!$item->product) {
                    // remove stale item
                    $item->delete();
                    continue;
                }
                if ($item->product->count <= 0) {
                    $item->delete();
                    continue;
                }
                if ($item->quantity > $item->product->count) {
                    return response()->json([
                        'status'=>'failed',
                        'message'=>"Only {$item->product->count} qty available for product {$item->product->id}"
                    ], 400);
                }
                $subtotal += $item->item_price * $item->quantity;
            }

            if ($subtotal <= 0) {
                return response()->json(['status'=>'failed','message'=>'Invalid cart items'], 400);
            }

            $shipping = $subtotal < 50 ? 4.99 : 0;
            $total = round($subtotal + $shipping, 2);

            DB::beginTransaction();

            // create order (store UTC datetime)
            $nowUtc = Carbon::now('UTC');

            $order = Order::create([
                'unique_order_id'  => 'ORD'.time().rand(10,99),
                'invoice_no'       => 'INV'.time().rand(10,99),
                'user_id'          => $userId,
                'user_address_id'  => $user_address->id,
                'address_data'     => json_encode($user_address),
                'order_datetime'   => $nowUtc, // use single datetime column
                'subtotal_amount'  => $subtotal,
                'shipping_amount'  => $shipping,
                'payable_amount'   => $total,
                'payment_mode'     => 2, // stripe
                'last_status_id'   => OrderStatusInterface::Pending
            ]);

            // create order_details (store cart_id when available)
            foreach ($cartItems as $item) {
                OrderDetails::create([
                    'order_id'            => $order->id,
                    'cart_id'             => $item->id ?? null, // requires migration; nullable ok
                    'product_id'          => $item->product_id,
                    'buy_quantity'        => $item->quantity,
                    'product_unit_price'  => $item->item_price,
                    'product_json'        => json_encode($item->product)
                ]);
            }

            // create or reuse stripe customer
            Stripe::setApiKey(config('services.stripe.secret'));

            if ($authUser->stripe_customer_id) {
                $stripe_customer_id = $authUser->stripe_customer_id;
            } else {
                $customer = Customer::create([
                    'name'  => trim(($authUser->first_name ?? '') . ' ' . ($authUser->last_name ?? '')),
                    'email' => $authUser->email,
                    'metadata' => ['user_id' => $authUser->id]
                ]);
                $stripe_customer_id = $customer->id;
                $authUser->update(['stripe_customer_id' => $stripe_customer_id]);
            }

            // create PaymentIntent - restrict to card (no redirects)
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) round($total * 100),
                'currency' => config('app.currency', 'usd'),
                'customer' => $stripe_customer_id,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never'
                ],
                'metadata' => [
                    'order_id' => (string)$order->id,
                    'user_id'  => (string)$userId,
                ]
            ]);

            // save payment record
            OrderPayment::create([
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $total,
                'payment_status' => 0 // pending
            ]);

            // link payment intent to order
            $order->update(['payment_intent_id' => $paymentIntent->id]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment Intent created successfully',
                'order_id' => $order->id,
                'client_secret' => $paymentIntent->client_secret
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('createOrderStripeIntent error: '.$e->getMessage());
            return response()->json(['status'=>'failed','message'=>$e->getMessage()], 500);
        }
    }

    /**
     * Optional: manual confirm (for testing). In production prefer webhook handler.
     * This will finalize the order (stock decrement + remove cart rows).
     */
    public function confirmPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_intent_id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>'failed','message'=>$validator->errors()->first()], 400);
            }

            // find payment record
            $payment = OrderPayment::where('payment_intent_id', $request->payment_intent_id)->first();
            if (!$payment) return response()->json(['status'=>'failed','message'=>'Payment not found'], 404);

            // idempotent: if already paid, return
            if ($payment->payment_status == 1) {
                return response()->json(['status'=>'success','message'=>'Payment already processed'], 200);
            }

            // finalize order (common logic extracted)
            $this->finalizeOrder($payment->order_id, $request->payment_intent_id);

            return response()->json(['status'=>'success','message'=>'Payment confirmed and order finalized'], 200);

        } catch (\Exception $e) {
            Log::error('confirmPayment error: '.$e->getMessage());
            return response()->json(['status'=>'failed','message'=>$e->getMessage()], 500);
        }
    }

    /**
     * Webhook endpoint you should register in Stripe dashboard:
     * - Handles payment_intent.succeeded (and optionally failed)
     */
    public function stripeWebhook(Request $request)
    {
        // you should use the Stripe signature to verify the event:
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');
        $endpointSecret = config('services.stripe.webhook_secret'); // set in .env

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return response('Invalid payload', 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('Invalid signature', 400);
        }

        // handle event types
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $pi = $event->data->object;
                $paymentIntentId = $pi->id;
                // look up payment record
                $payment = OrderPayment::where('payment_intent_id', $paymentIntentId)->first();
                if ($payment && $payment->payment_status != 1) {
                    try {
                        $this->finalizeOrder($payment->order_id, $paymentIntentId);
                    } catch (\Exception $e) {
                        Log::error('webhook finalizeOrder error: '.$e->getMessage());
                        // don't throw - respond 200 to webhook; consider retrying or alerting
                    }
                }
                break;

            // optionally handle other events (payment_intent.payment_failed, charge.refunded, etc.)
            default:
                Log::info("Unhandled stripe event: ".$event->type);
        }

        return response('Webhook Handled', 200);
    }

    /**
     * Finalize order: mark payment, change order status, decrement stock, remove cart rows.
     * Idempotent and transactional.
     */
    protected function finalizeOrder(int $orderId, string $paymentIntentId = null)
    {
        DB::beginTransaction();
        try {
            $order = Order::with('orderDetail')->findOrFail($orderId);

            // fetch payment row
            $payment = OrderPayment::where('order_id', $orderId)
                        ->when($paymentIntentId, fn($q) => $q->where('payment_intent_id', $paymentIntentId))
                        ->first();

            if (!$payment) {
                // in rare case create a payment row
                $payment = OrderPayment::create([
                    'order_id' => $orderId,
                    'payment_intent_id' => $paymentIntentId,
                    'amount' => $order->payable_amount,
                    'payment_status' => 1
                ]);
            } else {
                // idempotent: if already paid, nothing to do
                if ($payment->payment_status == 1) {
                    DB::commit();
                    return;
                }
                $payment->update(['payment_status' => 1]);
            }

            // update order status
            $order->update(['last_status_id' => OrderStatusInterface::Confirmed]);

            // reduce stock and check availability (do this in DB transaction)
            $orderDetails = OrderDetails::where('order_id', $orderId)->get();

            foreach ($orderDetails as $od) {
                // lock for update (optional depending on DB engine)
                $product = Product::lockForUpdate()->find($od->product_id);

                if (!$product) {
                    // if product deleted, mark item and continue
                    Log::warning("Product {$od->product_id} not found while finalizing order {$orderId}");
                    continue;
                }

                if ($product->count < $od->buy_quantity) {
                    // insufficient stock — decide business rule: you can either fail / mark partially shipped / backorder
                    // for now we throw to rollback
                    throw new \Exception("Insufficient stock for product {$product->id}");
                }

                // decrement stock
                $product->decrement('count', $od->buy_quantity);
            }

            // remove cart rows that were part of this order
            // Prefer deletion by cart_id if available; fallback to product_id
            $cartIds = OrderDetails::where('order_id', $orderId)->pluck('cart_id')->filter()->toArray();

            if (!empty($cartIds)) {
                Carts::whereIn('id', $cartIds)->delete();
            } else {
                // fallback: delete carts by product id and user (safer)
                $productIds = $orderDetails->pluck('product_id')->toArray();
                if (!empty($productIds)) {
                    Carts::where('user_id', $order->user_id)
                         ->whereIn('product_id', $productIds)
                         ->delete();
                }
            }

            DB::commit();

            // TODO: send order confirmation email / notification
            return;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("finalizeOrder error for order {$orderId}: ".$e->getMessage());
            throw $e;
        }
    }
}