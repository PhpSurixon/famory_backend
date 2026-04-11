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
use App\Models\FamilyTagId;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Interfaces\OrderStatusInterface;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;


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

            // validate US zip code (5 digits or ZIP+4 format)
            if (!preg_match('/^\d{5}(-\d{4})?$/', $user_address->zip_code)) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Orders can only be placed for US addresses. Please select a valid US zip code.'
                ], 400);
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
                    ->where('action_type', 'cart')
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
                    'tag_code'            => $item->tag_code ? $item->tag_code : null,
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
    public function confirmPaymentOLD(Request $request)
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

    public function confirmPayment(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'payment_intent_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $timezone        = $request->header('timezone', 'UTC');
            $paymentIntentId = $request->payment_intent_id;

            /*
            |-------------------------------------------
            | 1. Verify PaymentIntent status with Stripe
            |    Never trust the client — always confirm
            |    directly from Stripe's servers.
            |-------------------------------------------
            */

            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $pi = PaymentIntent::retrieve($paymentIntentId);
            } catch (\Exception $e) {
                Log::error("confirmPayment: Stripe retrieve failed [{$paymentIntentId}]: ".$e->getMessage());
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Unable to verify payment. Please try again.'
                ], 502);
            }

            /*
            |-------------------------------------------
            | 2. Handle each Stripe PI status
            |-------------------------------------------
            */

            if ($pi->status === 'processing') {
                // Payment is being processed (e.g. bank transfer) — ask app to poll
                return response()->json([
                    'status'  => 'processing',
                    'message' => 'Payment is being processed. Please wait.'
                ], 202);
            }

            if (in_array($pi->status, ['requires_payment_method', 'requires_confirmation', 'requires_action', 'canceled'])) {
                // Payment failed or was abandoned — cancel the order
                $payment = OrderPayment::where('payment_intent_id', $paymentIntentId)->first();
                if ($payment && $payment->payment_status != 1) {
                    $this->cancelOrder($payment->order_id, $paymentIntentId, "PI status: {$pi->status}");
                }
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Payment was not completed. Please place a new order.'
                ], 402);
            }

            if ($pi->status !== 'succeeded') {
                // Unknown / unexpected status
                Log::warning("confirmPayment: unexpected PI status [{$pi->status}] for [{$paymentIntentId}]");
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Unexpected payment status. Please contact support.'
                ], 400);
            }

            /*
            |-------------------------------------------
            | 3. PI succeeded — find local payment record
            |-------------------------------------------
            */

            $payment = OrderPayment::where('payment_intent_id', $paymentIntentId)->first();

            if (!$payment) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Payment record not found.'
                ], 404);
            }

            /*
            |-------------------------------------------
            | 4. Check order is not already failed/cancelled
            |    (webhook may have processed a failure first)
            |-------------------------------------------
            */

            $order = Order::with(['orderDetail.product'])
                        ->where('id', $payment->order_id)
                        ->first();

            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Order not found.'
                ], 404);
            }

            if (in_array($order->last_status_id, [OrderStatusInterface::Cancelled, OrderStatusInterface::Failed])) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'This order was already cancelled. Please place a new order.'
                ], 409);
            }

            /*
            |-------------------------------------------
            | 5. Finalize order if not already confirmed
            |    Race condition is handled inside
            |    finalizeOrder via DB pessimistic lock.
            |-------------------------------------------
            */

            if ($payment->payment_status != 1) {
                $this->finalizeOrder($payment->order_id, $paymentIntentId);

                // Re-fetch order after finalization to get updated status
                $order = Order::with(['orderDetail.product'])
                            ->where('id', $payment->order_id)
                            ->first();
            }

            /*
            |-------------------------------------------
            | 6. Return order data
            |-------------------------------------------
            */

            $orderDateTime = Carbon::parse($order->order_datetime, 'UTC')
                                ->setTimezone($timezone)
                                ->format('Y-m-d H:i:s');

            $order_data = [
                'id'              => $order->id,
                'unique_order_id' => $order->unique_order_id,
                'order_datetime'  => $orderDateTime,
                'payable_amount'  => $order->payable_amount,
                'payment_mode'    => $order->payment_mode == 2 ? 'Online' : 'COD',
                'order_status'    => $order->order_status
            ];

            return response()->json([
                'status'  => 'success',
                'message' => 'Your order placed successfully',
                'data'    => $order_data
            ], 200);

        } catch (\Exception $e) {

            Log::error('confirmPayment error: '.$e->getMessage());

            return response()->json([
                'status'  => 'failed',
                'message' => 'Something went wrong'
            ], 500);
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
                $payment = OrderPayment::where('payment_intent_id', $pi->id)->first();
                if ($payment && $payment->payment_status != 1) {
                    try {
                        $this->finalizeOrder($payment->order_id, $pi->id);
                    } catch (\Exception $e) {
                        Log::error('webhook finalizeOrder error: '.$e->getMessage());
                    }
                }
                break;

            /*
            |------------------------------------------------------------------
            | Payment Failed — card declined, insufficient funds, etc.
            |------------------------------------------------------------------
            */
            case 'payment_intent.payment_failed':
                $pi = $event->data->object;
                $failReason = $pi->last_payment_error->message ?? 'Payment failed';
                Log::info("payment_intent.payment_failed [{$pi->id}]: {$failReason}");

                $payment = OrderPayment::where('payment_intent_id', $pi->id)->first();
                if ($payment && $payment->payment_status != 1) {
                    try {
                        $this->cancelOrder($payment->order_id, $pi->id, $failReason);
                    } catch (\Exception $e) {
                        Log::error('webhook cancelOrder (payment_failed) error: '.$e->getMessage());
                    }
                }
                break;

            /*
            |------------------------------------------------------------------
            | Payment Intent Canceled — Stripe auto-cancels expired intents
            | or user abandoned checkout and intent was explicitly canceled
            |------------------------------------------------------------------
            */
            case 'payment_intent.canceled':
                $pi = $event->data->object;
                $cancelReason = $pi->cancellation_reason ?? 'canceled';
                Log::info("payment_intent.canceled [{$pi->id}]: {$cancelReason}");

                $payment = OrderPayment::where('payment_intent_id', $pi->id)->first();
                if ($payment && $payment->payment_status != 1) {
                    try {
                        $this->cancelOrder($payment->order_id, $pi->id, "Intent canceled: {$cancelReason}");
                    } catch (\Exception $e) {
                        Log::error('webhook cancelOrder (intent_canceled) error: '.$e->getMessage());
                    }
                }
                break;

            default:
                Log::info("Unhandled stripe event: ".$event->type);
        }

        return response('Webhook Handled', 200);
    }

    /**
     * Finalize order: mark payment, change order status, decrement stock, remove cart rows.
     * Idempotent and transactional.
     */
    protected function finalizeOrderOLD(int $orderId, string $paymentIntentId = null)
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

    protected function finalizeOrder(int $orderId, string $paymentIntentId = null)
    {
        DB::beginTransaction();

        try {

            $order = Order::with(['orderDetail.product','user'])->findOrFail($orderId);

            /*
            |---------------------------------------
            | Check Payment Record
            |---------------------------------------
            */

            // lockForUpdate prevents webhook + confirmPayment from
            // both passing the payment_status check simultaneously
            $payment = OrderPayment::where('order_id', $orderId)
                        ->when($paymentIntentId, fn($q) => $q->where('payment_intent_id', $paymentIntentId))
                        ->lockForUpdate()
                        ->first();

            if (!$payment) {

                $payment = OrderPayment::create([
                    'order_id'          => $orderId,
                    'payment_intent_id' => $paymentIntentId,
                    'amount'            => $order->payable_amount,
                    'payment_status'    => 1
                ]);

            } else {

                // Already finalized (other process won the race) — exit cleanly
                if ($payment->payment_status == 1) {
                    DB::commit();
                    return;
                }

                $payment->update(['payment_status' => 1]);
            }

            /*
            |---------------------------------------
            | Update Order Status
            |---------------------------------------
            */

            $order->update([
                'last_status_id' => OrderStatusInterface::Confirmed
            ]);

            /*
            |---------------------------------------
            | Reduce Product Stock
            |---------------------------------------
            */

            $orderDetails = OrderDetails::where('order_id', $orderId)->get();

            foreach ($orderDetails as $od) {

                $product = Product::lockForUpdate()->find($od->product_id);

                if (!$product) {
                    Log::warning("Product {$od->product_id} missing for order {$orderId}");
                    continue;
                }

                if ($product->count < $od->buy_quantity) {
                    throw new \Exception("Insufficient stock for product {$product->id}");
                }

                $product->decrement('count', $od->buy_quantity);

                // Assign a unique Physical Tag code per order detail item
                if(!$od->tag_code) 
                {    
                    $od->update(['tag_code' => $this->generatePhysicalTagCode()]);
                }
            }

            /*
            |---------------------------------------
            | Remove Cart Items
            |---------------------------------------
            */

            $cartIds = OrderDetails::where('order_id', $orderId)
                        ->pluck('cart_id')
                        ->filter()
                        ->toArray();

            if (!empty($cartIds)) {

                Carts::whereIn('id', $cartIds)->delete();

            } else {

                $productIds = $orderDetails->pluck('product_id')->toArray();

                Carts::where('user_id', $order->user_id)
                    ->whereIn('product_id', $productIds)
                    ->delete();
            }

            DB::commit();

            /*
            |---------------------------------------
            | Generate Invoice PDF
            |---------------------------------------
            */

            $invoiceDir = storage_path('app/invoices');

            if (!file_exists($invoiceDir)) {
                mkdir($invoiceDir, 0777, true);
            }

            $order->address_data = json_decode($order->address_data, true);

            $invoiceFile = $invoiceDir.'/invoice_'.$order->unique_order_id.'.pdf';

            $pdf = Pdf::loadView('invoice.order_invoice', [
                'order' => $order
            ]);

            $pdf->save($invoiceFile);

            /*
            |---------------------------------------
            | Send Order Email with Invoice
            |---------------------------------------
            */

            Mail::to($order->user->email)
                ->queue(new OrderConfirmationMail($order, $invoiceFile));

            return;

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error("finalizeOrder error for order {$orderId}: ".$e->getMessage());

            throw $e;
        }
    }

    /**
     * Cancel an order that failed payment or was never paid.
     * Stock is NOT restored because Pending orders never had stock decremented.
     */
    protected function cancelOrder(int $orderId, string $paymentIntentId = null, string $reason = 'Payment failed')
    {
        DB::beginTransaction();

        try {

            $order = Order::findOrFail($orderId);

            // Already finalized — do nothing
            if ($order->last_status_id === OrderStatusInterface::Confirmed) {
                DB::commit();
                return;
            }

            // Already cancelled/failed — idempotent
            if (in_array($order->last_status_id, [OrderStatusInterface::Cancelled, OrderStatusInterface::Failed])) {
                DB::commit();
                return;
            }

            /*
            |---------------------------------------
            | Mark payment as failed
            |---------------------------------------
            */

            if ($paymentIntentId) {
                OrderPayment::where('order_id', $orderId)
                    ->where('payment_intent_id', $paymentIntentId)
                    ->update(['payment_status' => 2]); // 2 = failed
            }

            /*
            |---------------------------------------
            | Update order status to Failed
            |---------------------------------------
            */

            $order->update(['last_status_id' => OrderStatusInterface::Failed]);

            DB::commit();

            Log::info("Order {$orderId} marked as Failed. Reason: {$reason}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("cancelOrder error for order {$orderId}: ".$e->getMessage());
            throw $e;
        }
    }

    public function orderList(Request $request)
    {
        try {

            $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'status' => 'nullable|integer',
                'limit'  => 'nullable|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $limit  = $request->limit ?? 10;
            $status = $request->status;

            $query = Order::with(['orderDetail.product'])
                        ->where('user_id', $authUser->id);

            /*
            |---------------------------------------
            | Filter by order status
            |---------------------------------------
            */

            if (!empty($status)) {
                $query->where('last_status_id', $status);
            }else{
                $query->where('last_status_id', '!=', 1);
            }

            /*
            |---------------------------------------
            | Pagination
            |---------------------------------------
            */

            $orders = $query->orderBy('order_datetime', 'desc')
                            ->paginate($limit);

            $timezone = $request->header('timezone', 'UTC');

            $order_data = $orders->map(function ($order) use ($timezone) {

                $orderDateTime = Carbon::parse($order->order_datetime, 'UTC')
                                        ->setTimezone($timezone)
                                        ->format('Y-m-d H:i:s');

                $order_created_at = optional($order->created_at)
                    ? Carbon::parse($order->created_at, 'UTC')
                        ->setTimezone($timezone)
                        ->format('d, M Y H:i:s A')
                    : null;

                $order_updated_at = optional($order->updated_at)
                    ? Carbon::parse($order->updated_at, 'UTC')
                        ->setTimezone($timezone)
                        ->format('d, M Y H:i:s A')
                    : null;

                return [
                    'id' => $order->id,
                    'unique_order_id' => $order->unique_order_id,
                    'invoice_no' => $order->invoice_no,
                    'order_datetime' => $orderDateTime,
                    'payable_amount' => $order->payable_amount,
                    'payment_mode' => $order->payment_mode == 2 ? 'Online' : 'COD',
                    'last_status_id' => $order->last_status_id,
                    'order_status' => $order->order_status,
                    'order_status_message' => $order->order_status_message,
                    'order_created_at' => $order_created_at,
                    'order_updated_at' => $order_updated_at,
                    'address_data' => json_decode($order->address_data, true),

                    'order_items' => $order->orderDetail->map(function ($od) {
                        return [
                            'product_id' => $od->product_id,
                            'buy_quantity' => $od->buy_quantity,
                            'product_unit_price' => $od->product_unit_price,
                            'tag_code' => $od->tag_code,
                            'product_json' => json_decode($od->product_json, true)
                        ];
                    })
                ];
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Orders list fetched successfully',
                // 'data'    => $orders->items(),
                'data'    => $order_data,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page'    => $orders->lastPage(),
                    'per_page'     => $orders->perPage(),
                    'total'        => $orders->total()
                ]
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'failed',
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function orderDetailOLD(Request $request)
    {
        try {
            $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $timezone = $request->header('timezone', 'UTC');

            /*
            |---------------------------------------
            | Get Order with Relations
            |---------------------------------------
            */
            $order = Order::with(['orderDetail.product'])
                ->where('id', $request->order_id)
                ->where('user_id', $authUser->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Order not found'
                ], 404);
            }

            /*
            |---------------------------------------
            | Convert Order DateTime to User TZ
            |---------------------------------------
            */
            $orderDateTime = optional($order->order_datetime)
                ? Carbon::parse($order->order_datetime, 'UTC')
                    ->setTimezone($timezone)
                    ->format('Y-m-d H:i:s')
                : null;

            $order_created_at = optional($order->created_at)
                ? Carbon::parse($order->created_at, 'UTC')
                    ->setTimezone($timezone)
                    ->format('d, M Y H:i:s A')
                : null;

            $order_updated_at = optional($order->updated_at)
                ? Carbon::parse($order->updated_at, 'UTC')
                    ->setTimezone($timezone)
                    ->format('d, M Y H:i:s A')
                : null;

            /*
            |---------------------------------------
            | Avoid N+1 Query (IMPORTANT FIX)
            |---------------------------------------
            */
            $tagCodes = $order->orderDetail->pluck('tag_code')->filter()->toArray();

            $registeredTags = FamilyTagId::whereIn('family_tag_id', $tagCodes)
                ->where('created_user_id', $authUser->id)
                ->pluck('family_tag_id')
                ->toArray();

            /*
            |---------------------------------------
            | Transform Order Data
            |---------------------------------------
            */
            $order_data = [
                'id'                => $order->id,
                'unique_order_id'   => $order->unique_order_id,
                'invoice_no'        => $order->invoice_no,
                'order_datetime'    => $orderDateTime,
                'payable_amount'    => $order->payable_amount,
                'payment_mode'      => $order->payment_mode == 2 ? 'Online' : 'COD',
                'last_status_id'    => $order->last_status_id,
                'order_status'      => $order->order_status,
                'order_status_message' => $order->order_status_message,
                'waybill'               => $order->waybill,
                'tracking_url'          => $order->tracking_url,
                'address_data'          => json_decode($order->address_data, true),
                'order_created_at'    => $order_created_at,
                'order_updated_at'    => $order_updated_at,
                'order_items' => $order->orderDetail->map(function ($od) use ($registeredTags) {
                    return [
                        'product_id' => $od->product_id,
                        'buy_quantity' => $od->buy_quantity,
                        'product_unit_price' => $od->product_unit_price,
                        'tag_code' => $od->tag_code,

                        // optimized check (NO DB HIT)
                        'tag_register_as_digital' => in_array($od->tag_code, $registeredTags),

                        'product_json' => json_decode($od->product_json, true)
                    ];
                })
            ];

            return response()->json([
                'status'  => 'success',
                'message' => 'Order detail fetched successfully',
                'data'    => $order_data
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'failed',
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Order Detail (with tag_name from FamilyTagId)
     */
    public function orderDetail(Request $request)
    {
        try {
            $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $timezone = $request->header('timezone', 'UTC');

            /*
            |---------------------------------------
            | Get Order with Relations
            |---------------------------------------
            */
            $order = Order::with(['orderDetail.product'])
                ->where('id', $request->order_id)
                ->where('user_id', $authUser->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Order not found'
                ], 404);
            }

            /*
            |---------------------------------------
            | Convert Order DateTime to User TZ
            |---------------------------------------
            */
            $orderDateTime = optional($order->order_datetime)
                ? Carbon::parse($order->order_datetime, 'UTC')
                    ->setTimezone($timezone)
                    ->format('Y-m-d H:i:s')
                : null;

            $order_created_at = optional($order->created_at)
                ? Carbon::parse($order->created_at, 'UTC')
                    ->setTimezone($timezone)
                    ->format('d, M Y H:i:s A')
                : null;

            $order_updated_at = optional($order->updated_at)
                ? Carbon::parse($order->updated_at, 'UTC')
                    ->setTimezone($timezone)
                    ->format('d, M Y H:i:s A')
                : null;

            /*
            |---------------------------------------
            | Batch lookup: tag_register + tag_name
            |---------------------------------------
            */
            $tagCodes = $order->orderDetail->pluck('tag_code')->filter()->unique()->values()->toArray();

            $registeredTags = [];
            $familyTags = [];

            if (!empty($tagCodes)) {
                $familyTagRecords = FamilyTagId::whereIn('family_tag_id', $tagCodes)
                    ->get(['family_tag_id', 'title', 'created_user_id']);

                foreach ($familyTagRecords as $ft) {
                    $familyTags[$ft->family_tag_id] = $ft->title;
                    if ($ft->created_user_id == $authUser->id) {
                        $registeredTags[] = $ft->family_tag_id;
                    }
                }
            }

            /*
            |---------------------------------------
            | Transform Order Data
            |---------------------------------------
            */
            $order_data = [
                'id'                => $order->id,
                'unique_order_id'   => $order->unique_order_id,
                'invoice_no'        => $order->invoice_no,
                'order_datetime'    => $orderDateTime,
                'payable_amount'    => $order->payable_amount,
                'payment_mode'      => $order->payment_mode == 2 ? 'Online' : 'COD',
                'last_status_id'    => $order->last_status_id,
                'order_status'      => $order->order_status,
                'order_status_message' => $order->order_status_message,
                'waybill'               => $order->waybill,
                'tracking_url'          => $order->tracking_url,
                'address_data'          => json_decode($order->address_data, true),
                'order_created_at'    => $order_created_at,
                'order_updated_at'    => $order_updated_at,
                'order_items' => $order->orderDetail->map(function ($od) use ($registeredTags, $familyTags) {
                    return [
                        'product_id' => $od->product_id,
                        'buy_quantity' => $od->buy_quantity,
                        'product_unit_price' => $od->product_unit_price,
                        'tag_code' => $od->tag_code,
                        'tag_name' => $od->tag_code ? ($familyTags[$od->tag_code] ?? null) : null,
                        'tag_register_as_digital' => in_array($od->tag_code, $registeredTags),
                        'product_json' => json_decode($od->product_json, true)
                    ];
                })
            ];

            return response()->json([
                'status'  => 'success',
                'message' => 'Order detail fetched successfully',
                'data'    => $order_data
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'failed',
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // public function orderDetail(Request $request)
    // {
    //     try {

    //         $authUser = Auth::user();

    //         $validator = Validator::make($request->all(), [
    //             'order_id' => 'required|exists:orders,id'
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status'  => 'failed',
    //                 'message' => $validator->errors()->first()
    //             ], 400);
    //         }

    //         $timezone = $request->header('timezone', 'UTC');

    //         $order = Order::with(['orderDetail.product'])
    //                     ->where('id', $request->order_id)
    //                     ->where('user_id', $authUser->id)
    //                     ->first();

    //         if (!$order) {
    //             return response()->json([
    //                 'status'  => 'failed',
    //                 'message' => 'Order not found'
    //             ], 404);
    //         }

    //         /*
    //         |---------------------------------------
    //         | Convert Order DateTime to User TZ
    //         |---------------------------------------
    //         */

    //         $orderDateTime = Carbon::parse($order->order_datetime, 'UTC')
    //                             ->setTimezone($timezone)
    //                             ->format('Y-m-d H:i:s');

    //         /*
    //         |---------------------------------------
    //         | Transform Order Data
    //         |---------------------------------------
    //         */

    //         $order_data = [
    //             'id' => $order->id,
    //             'unique_order_id' => $order->unique_order_id,
    //             'invoice_no' => $order->invoice_no,
    //             'order_datetime' => $orderDateTime,
    //             'payable_amount' => $order->payable_amount,
    //             'payment_mode' => $order->payment_mode == 2 ? 'Online' : 'COD',
    //             'last_status_id' => $order->last_status_id,
    //             'order_status' => $order->order_status,
    //             'waybill' => $order->waybill,
    //             'tracking_url'=> $order->tracking_url,
    //             'address_data' => json_decode($order->address_data, true),
    //             'order_items' => $order->orderDetail->map(function($od)use($authUser){

    //                 $tag_register_as_digital = FamilyTagId::where('family_tag_id',$od->tag_code)
    //                                                       ->where('created_user_id',$authUser->id)
    //                                                       ->exists();
    //                 return [
    //                     'product_id' => $od->product_id,
    //                     'buy_quantity' => $od->buy_quantity,
    //                     'product_unit_price' => $od->product_unit_price,
    //                     'tag_code'           => $od->tag_code,
    //                     'tag_register_as_digital'           => $tag_register_as_digital,
    //                     'product_json' => json_decode($od->product_json, true)
    //                 ];
    //             })
    //         ];

    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Order detail fetched successfully',
    //             'data'    => $order_data
    //         ], 200);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status'  => 'failed',
    //             'message' => 'Something went wrong',
    //             'error'   => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function downloadInvoiceNew(Request $request)
    {
        try {

            // $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $timezone = $request->header('timezone', 'UTC');

            $order = Order::with(['orderDetail.product'])
                        ->where('id', $request->order_id)
                        // ->where('user_id', $authUser->id)
                        ->first();

            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Order not found'
                ], 404);
            }

            /*
            |---------------------------------------
            | Convert Order DateTime
            |---------------------------------------
            */

            $order->order_datetime = Carbon::parse($order->order_datetime, 'UTC')
                                    ->setTimezone($timezone)
                                    ->format('Y-m-d H:i:s');

            $order->address_data = json_decode($order->address_data, true);

            /*
            |---------------------------------------
            | Generate PDF
            |---------------------------------------
            */

            $pdf = Pdf::loadView('invoice.order_invoice', [
                'order' => $order
            ]);

            /*
            |---------------------------------------
            | Return Download
            |---------------------------------------
            */

            return $pdf->download('invoice_'.$order->unique_order_id.'.pdf');

        } catch (\Exception $e) {
            
            return response()->json([
                'status'  => 'failed',
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique Physical Tag code with PT prefix.
     * Format: PT + [0-9] + 6 digits  e.g. PT3045821
     * Retries until a code not already used in order_details is found.
     */
    protected function generatePhysicalTagCode(): string
    {
        do {
            $code = 'PT' . rand(0, 9) . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (OrderDetails::where('tag_code', $code)->exists());

        return $code;
    }

    public function downloadInvoice(Request $request)
    {
        try {

            $authUser = Auth::user();

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $timezone = $request->header('timezone', 'UTC');

            $order = Order::with(['orderDetail.product'])
                        ->where('id', $request->order_id)
                        ->where('user_id', $authUser->id)
                        ->first();

            if (!$order) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Order not found'
                ], 404);
            }

            /*
            |---------------------------------------
            | Convert Order DateTime
            |---------------------------------------
            */

            $order->order_datetime = Carbon::parse($order->order_datetime, 'UTC')
                                    ->setTimezone($timezone)
                                    ->format('Y-m-d H:i:s');

            $order->address_data = json_decode($order->address_data, true);

            /*
            |---------------------------------------
            | Generate PDF
            |---------------------------------------
            */

            $pdf = Pdf::loadView('invoice.order_invoice', [
                'order' => $order
            ]);

            /*
            |---------------------------------------
            | Return Download
            |---------------------------------------
            */

            return $pdf->download('invoice_'.$order->unique_order_id.'.pdf');

        } catch (\Exception $e) {
            
            return response()->json([
                'status'  => 'failed',
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


}