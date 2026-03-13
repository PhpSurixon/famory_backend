<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f5f5;
    padding: 20px;
}
.container {
    max-width: 650px;
    background: #ffffff;
    margin: auto;
    padding: 30px;
    border-radius: 6px;
}
.header {
    text-align: center;
    border-bottom: 1px solid #eee;
    padding-bottom: 20px;
}
.logo {
    height: 50px;
}
.title {
    font-size: 22px;
    margin-top: 10px;
    color: #333;
}
.badge {
    display: inline-block;
    background: #28a745;
    color: #fff;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 14px;
    margin-top: 8px;
}
.section {
    margin-top: 25px;
}
.info-box {
    background: #fafafa;
    border: 1px solid #eee;
    border-radius: 5px;
    padding: 15px 20px;
}
.info-box p {
    margin: 6px 0;
    font-size: 14px;
    color: #444;
}
.waybill-box {
    background: #eaf4ff;
    border: 1px solid #b3d7ff;
    border-radius: 5px;
    padding: 15px 20px;
    margin-top: 20px;
    text-align: center;
}
.waybill-box .label {
    font-size: 13px;
    color: #555;
    margin-bottom: 6px;
}
.waybill-box .waybill-number {
    font-size: 22px;
    font-weight: 700;
    color: #0d397f;
    letter-spacing: 2px;
}
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
.table th {
    background: #f3f3f3;
    padding: 10px;
    border: 1px solid #ddd;
    font-size: 13px;
    text-align: left;
}
.table td {
    padding: 10px;
    border: 1px solid #ddd;
    font-size: 13px;
}
.footer {
    text-align: center;
    margin-top: 40px;
    font-size: 12px;
    color: #777;
    border-top: 1px solid #eee;
    padding-top: 20px;
}
</style>
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <img src="https://admin-dev.famoryapp.com/assets/img/app_logo.png" class="logo" alt="Famory">
        <div class="title">Your Order Has Been Shipped!</div>
        <span class="badge">Shipped</span>
    </div>

    <!-- GREETING -->
    <div class="section">
        <p>Hello <strong>{{ $order->user->first_name ?? 'Customer' }}</strong>,</p>
        <p>Great news! Your order is on its way. Below you will find your shipment details and tracking information.</p>
    </div>

    <!-- WAYBILL -->
    <div class="waybill-box">
        <div class="label">Waybill / Tracking Number</div>
        <div class="waybill-number">{{ $waybill }}</div>
        @if(!empty($trackingUrl))
        <div style="margin-top:12px;">
            <a href="{{ $trackingUrl }}" target="_blank"
               style="display:inline-block;background:#0d397f;color:#fff;padding:8px 20px;border-radius:4px;font-size:14px;text-decoration:none;">
                Track My Order
            </a>
        </div>
        @endif
    </div>

    <!-- ORDER INFO -->
    <div class="section">
        <div class="info-box">
            <p><strong>Order ID:</strong> {{ $order->unique_order_id }}</p>
            <p><strong>Invoice No:</strong> {{ $order->invoice_no }}</p>
            @if($order->order_datetime)
            <p><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($order->order_datetime)->format('d M Y') }}</p>
            @endif
            <p><strong>Amount Paid:</strong> ${{ number_format($order->payable_amount, 2) }}</p>
        </div>
    </div>

    <!-- ORDER ITEMS -->
    @if($order->orderDetail && $order->orderDetail->count())
    <div class="section">
        <strong>Items in this shipment</strong>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetail as $index => $item)
                @php $product = json_decode($item->product_json, true); @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product['name'] ?? 'Product' }}</td>
                    <td>{{ $item->buy_quantity }}</td>
                    <td>${{ number_format($item->product_unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- NOTE -->
    <div class="section">
        <p style="font-size:14px; color:#555;">
            If you have any questions about your shipment, please contact us at
            <a href="mailto:support@famoryapp.com" style="color:#0d397f;">support@famoryapp.com</a>.
        </p>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>© {{ date('Y') }} FamoryApp. All rights reserved.</p>
    </div>

</div>

</body>
</html>
