<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, Helvetica, sans-serif; background: #f5f5f5; padding: 20px; }
.container { max-width: 650px; background: #ffffff; margin: auto; padding: 30px; border-radius: 6px; }
.header { text-align: center; border-bottom: 1px solid #eee; padding-bottom: 20px; }
.logo { height: 50px; }
.title { font-size: 22px; margin-top: 10px; color: #333; }
.badge { display: inline-block; background: #dc3545; color: #fff; padding: 6px 16px; border-radius: 20px; font-size: 14px; margin-top: 8px; }
.section { margin-top: 25px; }
.info-box { background: #fafafa; border: 1px solid #eee; border-radius: 5px; padding: 15px 20px; }
.info-box p { margin: 6px 0; font-size: 14px; color: #444; }
.refund-box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; padding: 15px 20px; margin-top: 20px; text-align: center; }
.refund-box .label { font-size: 13px; color: #555; margin-bottom: 6px; }
.refund-box .refund-id { font-size: 16px; font-weight: 700; color: #856404; letter-spacing: 1px; word-break: break-all; }
.footer { text-align: center; margin-top: 40px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 20px; }
</style>
</head>
<body>

<div class="container">

    <div class="header">
        <img src="https://admin-dev.famoryapp.com/assets/img/app_logo.png" class="logo" alt="Famory">
        <div class="title">Your Order Has Been Cancelled</div>
        <span class="badge">Cancelled</span>
    </div>

    <div class="section">
        <p>Hello <strong>{{ $order->user->first_name ?? 'Customer' }}</strong>,</p>
        <p>Your order has been cancelled. We're sorry for any inconvenience.</p>
        @if($cancelReason)
        <p style="font-size:14px;color:#555;"><strong>Reason:</strong> {{ $cancelReason }}</p>
        @endif
    </div>

    <div class="section">
        <div class="info-box">
            <p><strong>Order ID:</strong> {{ $order->unique_order_id }}</p>
            <p><strong>Invoice No:</strong> {{ $order->invoice_no }}</p>
            @if($order->order_datetime)
            <p><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->order_datetime)->format('d M Y') }}</p>
            @endif
            <p><strong>Amount:</strong> ${{ number_format($order->payable_amount, 2) }}</p>
        </div>
    </div>

    @if($order->payment_mode == 2)
    <div class="refund-box">
        <div class="label">Refund Status</div>
        @if($refundId)
            <p style="font-size:14px;color:#444;margin:6px 0;">
                A refund of <strong>${{ number_format($order->payable_amount, 2) }}</strong> has been initiated to your original payment method.
            </p>
            <div class="label" style="margin-top:10px;">Refund Reference</div>
            <div class="refund-id">{{ $refundId }}</div>
            <p style="font-size:12px;color:#777;margin-top:8px;">Refunds typically appear within 5–10 business days.</p>
        @else
            <p style="font-size:14px;color:#444;margin:6px 0;">
                Your refund will be processed within 7 working days to your original payment method.
            </p>
        @endif
    </div>
    @endif

    <div class="section">
        <p style="font-size:14px; color:#555;">
            If you have any questions, please contact us at
            <a href="mailto:support@famoryapp.com" style="color:#0d397f;">support@famoryapp.com</a>.
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} FamoryApp. All rights reserved.</p>
    </div>

</div>

</body>
</html>
