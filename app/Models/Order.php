<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Interfaces\OrderStatusInterface;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'unique_order_id',
        'invoice_no',
        'user_id',
        'user_address_id',
        'address_data',
        'order_datetime',
        'last_status_id',
        'payment_mode',
        'subtotal_amount',
        'shipping_amount',
        'payable_amount',
        'payment_intent_id',
        'waybill',
        'tracking_url',
        'stripe_refund_id',
        'cancel_reason',
    ];

    public function orderDetail()
    {
       return $this->hasMany(OrderDetails::class, 'order_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getOrderStatusAttribute(){
        $statusId = $this->last_status_id; 
        $status = '';
        if($statusId == OrderStatusInterface::Pending) {
            $status = 'Pending';
        } elseif ($statusId == OrderStatusInterface::Confirmed) {
            $status = 'Confirmed';
        } elseif ($statusId == OrderStatusInterface::Shipped) {
            $status = 'Shipped';
        } elseif ($statusId == OrderStatusInterface::Delivered) {
            $status = 'Delivered';
        } elseif ($statusId == OrderStatusInterface::Not_Delivered) {
            $status = 'Not Delivered';
        } elseif ($statusId == OrderStatusInterface::Cancelled) {
            $status = 'Cancelled';
        } elseif ($statusId == OrderStatusInterface::Failed) {
            $status = 'Payment Failed';
        } elseif ($statusId == OrderStatusInterface::Refunded) {
            $status = 'Refunded';
        }
        return $status;
    }

    public function getOrderStatusMessageAttribute(){
        $statusId = $this->last_status_id; 
        $status_message = '';
        if($statusId == OrderStatusInterface::Pending) {
            $status_message = 'We are processing your payment.';
        } elseif ($statusId == OrderStatusInterface::Confirmed) {
            $status_message = 'Payment confirmed, Order Processing.';
        } elseif ($statusId == OrderStatusInterface::Shipped) {
            $status_message = 'Your order is shipped, Please check Tracking number.';
        } elseif ($statusId == OrderStatusInterface::Delivered) {
            $status_message = 'Your order is Delivered.';
        } elseif ($statusId == OrderStatusInterface::Not_Delivered) {
            $status_message = 'Could Not be Delivered, Contact Supports.';
        } elseif ($statusId == OrderStatusInterface::Cancelled) {
            $status_message = 'Order Cancelled. Your refund will be processed in 7 working days';
        } elseif ($statusId == OrderStatusInterface::Failed) {
            $status_message = 'Payment Failed,that why order not processed';
        } elseif ($statusId == OrderStatusInterface::Refunded) {
            $status_message = 'Order cancelled and refund has been processed to your original payment method.';
        }
        return $status_message;
    }
    
    

}