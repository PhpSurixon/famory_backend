<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Http\Interfaces\OrderStatusInterface;
use App\Mail\OrderShippedMail;
use App\Mail\OrderCancelledMail;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Refund;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminOrderController extends Controller
{
    /**
     * List all orders with filters and pagination.
     *
     * GET /order-list
     *
     * Query params:
     *   status       (int)    – filter by last_status_id
     *   search       (string) – search by unique_order_id, invoice_no, or user email/name
     *   date_from    (date)   – filter orders on or after this date (Y-m-d, UTC)
     *   date_to      (date)   – filter orders on or before this date (Y-m-d, UTC)
     *   limit        (int)    – per page, default 15
     */
    public function orderList(Request $request)
    {
        $query = Order::with(['user:id,first_name,last_name,email', 'orderDetail'])
            ->orderBy('order_datetime', 'desc');

        if ($request->filled('status')) {
            $query->where('last_status_id', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('unique_order_id', 'like', "%{$search}%")
                  ->orWhere('invoice_no', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                         ->orWhere('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('order_datetime', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_datetime', '<=', $request->date_to);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.Order.orderList', compact('orders'));
    }

    /**
     * Show full order detail.
     *
     * GET /order/{id}/view
     */
    public function viewOrder(int $id)
    {
        $order = Order::with(['user', 'orderDetail'])->findOrFail($id);
        $order->address_data = json_decode($order->address_data, true);
        return view('admin.Order.viewOrder', compact('order'));
    }

    /**
     * Add waybill number and mark order as Shipped.
     * Sends a queued shipped notification email to the customer.
     *
     * POST /admin/order/update-shipping
     *
     * Body:
     *   order_id  (int, required)
     *   waybill   (string, required)
     */
    public function updateShipping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id'    => 'required|integer|exists:orders,id',
            'waybill'     => 'required|string|max:100',
            'tracking_url'=> 'nullable|url|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 400);
        }

        try {
            $order = Order::with(['user', 'orderDetail'])->findOrFail($request->order_id);

            if (!in_array($order->last_status_id, [
                OrderStatusInterface::Confirmed,
                OrderStatusInterface::Shipped,
            ])) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Order must be Confirmed to be marked as Shipped. Current status: ' . $order->order_status,
                ], 422);
            }

            $alreadyShipped = $order->last_status_id == OrderStatusInterface::Shipped;

            $order->update([
                'waybill'        => $request->waybill,
                'tracking_url'   => $request->tracking_url,
                'last_status_id' => OrderStatusInterface::Shipped,
            ]);

            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)
                    ->queue(new OrderShippedMail($order, $request->waybill, $request->tracking_url));
            }

            return response()->json([
                'status'  => 'success',
                'message' => $alreadyShipped ? 'Shipping details updated successfully' : 'Order marked as Shipped and customer notified',
                'data' => [
                    'id'             => $order->id,
                    'unique_order_id'=> $order->unique_order_id,
                    'last_status_id' => $order->last_status_id,
                    'order_status'   => $order->order_status,
                    'waybill'        => $order->waybill,
                    'tracking_url'   => $order->tracking_url,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('AdminOrderController@updateShipping: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * Cancel an order. Issues a Stripe refund for online payments and notifies the customer.
     *
     * POST /admin/order/cancel
     *
     * Body:
     *   order_id       (int, required)
     *   cancel_reason  (string, optional)
     */
    public function cancelOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id'      => 'required|integer|exists:orders,id',
            'cancel_reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 400);
        }

        try {
            $order = Order::with(['user', 'orderDetail'])->findOrFail($request->order_id);

            if (!in_array($order->last_status_id, [
                OrderStatusInterface::Confirmed,
                OrderStatusInterface::Pending,
            ])) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Only Pending or Confirmed orders can be cancelled. Current status: ' . $order->order_status,
                ], 422);
            }

            $refundId = null;

            // Stripe refund for online payments
            if ($order->payment_mode == 2 && $order->payment_intent_id) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $refund   = Refund::create(['payment_intent' => $order->payment_intent_id]);
                $refundId = $refund->id;
            }

            $order->update([
                'last_status_id'  => $refundId ? OrderStatusInterface::Refunded : OrderStatusInterface::Cancelled,
                'stripe_refund_id'=> $refundId,
                'cancel_reason'   => $request->cancel_reason,
            ]);

            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)
                    ->queue(new OrderCancelledMail($order, $refundId, $request->cancel_reason));
            }

            return response()->json([
                'status'  => 'success',
                'message' => $refundId
                    ? 'Order cancelled and refund initiated successfully'
                    : 'Order cancelled successfully',
                'data' => [
                    'id'              => $order->id,
                    'unique_order_id' => $order->unique_order_id,
                    'last_status_id'  => $order->last_status_id,
                    'order_status'    => $order->order_status,
                    'stripe_refund_id'=> $refundId,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('AdminOrderController@cancelOrder: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * Mark a shipped order as Delivered or Not Delivered.
     *
     * POST /admin/order/update-delivery
     *
     * Body:
     *   order_id  (int, required)
     *   status    (int, required) – 4 = Delivered, 5 = Not Delivered
     */
    public function updateDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'status'   => 'required|integer|in:' . OrderStatusInterface::Delivered . ',' . OrderStatusInterface::Not_Delivered,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 400);
        }

        try {
            $order = Order::with('user')->findOrFail($request->order_id);

            if ($order->last_status_id !== OrderStatusInterface::Shipped) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Only Shipped orders can be marked as Delivered or Not Delivered. Current status: ' . $order->order_status,
                ], 422);
            }

            $order->update(['last_status_id' => (int) $request->status]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Order status updated to ' . $order->order_status,
                'data' => [
                    'id'             => $order->id,
                    'unique_order_id'=> $order->unique_order_id,
                    'last_status_id' => $order->last_status_id,
                    'order_status'   => $order->order_status,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('AdminOrderController@updateDelivery: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * Generate PT tag_code for order detail items that are missing one.
     * Can target a single order or all confirmed orders.
     *
     * POST /admin/order/generate-tag-codes
     *
     * Body:
     *   order_id  (int, optional) – if provided, only process this order's items
     */
    public function generateTagCodes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'nullable|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 400);
        }

        try {
            $query = OrderDetails::whereNull('tag_code');

            if ($request->filled('order_id')) {
                $query->where('order_id', $request->order_id);
            }

            $items = $query->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'No order items found without a tag code.',
                    'updated' => 0,
                ], 200);
            }

            $updated = 0;
            foreach ($items as $item) {
                $item->update(['tag_code' => $this->generatePhysicalTagCode()]);
                $updated++;
            }

            return response()->json([
                'status'  => 'success',
                'message' => "{$updated} tag code(s) generated successfully.",
                'updated' => $updated,
            ], 200);

        } catch (\Exception $e) {
            Log::error('AdminOrderController@generateTagCodes: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * Download invoice PDF for an order.
     *
     * GET /admin/order/{id}/invoice
     */
    public function downloadInvoice(int $id)
    {
        $order = Order::with(['user', 'orderDetail'])->findOrFail($id);
        $order->address_data = json_decode($order->address_data, true);

        $pdf = Pdf::loadView('invoice.order_invoice', ['order' => $order]);
        return $pdf->download('invoice_' . $order->unique_order_id . '.pdf');
    }

    /**
     * Generate a unique Physical Tag code with PT prefix.
     * Format: PT + [0-9] + 6 digits  e.g. PT3045821
     */
    protected function generatePhysicalTagCode(): string
    {
        do {
            $code = 'PT' . rand(0, 9) . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (OrderDetails::where('tag_code', $code)->exists());

        return $code;
    }
}
