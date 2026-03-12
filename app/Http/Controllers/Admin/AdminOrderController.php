<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Interfaces\OrderStatusInterface;
use App\Mail\OrderShippedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
            'order_id' => 'required|integer|exists:orders,id',
            'waybill'  => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failed', 'message' => $validator->errors()->first()], 400);
        }

        try {
            $order = Order::with(['user', 'orderDetail'])->findOrFail($request->order_id);

            /*
            |--------------------------------------
            | Only confirmed orders can be shipped
            |--------------------------------------
            */
            if (!in_array($order->last_status_id, [
                OrderStatusInterface::Confirmed,
                OrderStatusInterface::Shipped, // allow waybill update if already shipped
            ])) {
                return response()->json([
                    'status'  => 'failed',
                    'message' => 'Order must be in Confirmed status to be marked as Shipped. Current status: ' . $order->order_status,
                ], 422);
            }

            $alreadyShipped = $order->last_status_id == OrderStatusInterface::Shipped;

            /*
            |--------------------------------------
            | Update waybill + status
            |--------------------------------------
            */
            $order->update([
                'waybill'        => $request->waybill,
                'last_status_id' => OrderStatusInterface::Shipped,
            ]);

            /*
            |--------------------------------------
            | Send shipped notification email (queue)
            |--------------------------------------
            */
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email)
                    ->queue(new OrderShippedMail($order, $request->waybill));
            }

            return response()->json([
                'status'  => 'success',
                'message' => $alreadyShipped
                    ? 'Waybill updated successfully'
                    : 'Order marked as Shipped and notification sent to customer',
                'data' => [
                    'id'             => $order->id,
                    'unique_order_id'=> $order->unique_order_id,
                    'last_status_id' => $order->last_status_id,
                    'order_status'   => $order->order_status,
                    'waybill'        => $order->waybill,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('AdminOrderController@updateShipping: ' . $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Something went wrong'], 500);
        }
    }
}
