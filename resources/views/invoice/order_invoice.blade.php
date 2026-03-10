<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

body{
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
    color:#333;
}

.container{
    width:100%;
}

.header{
    width:100%;
    margin-bottom:20px;
}

.company{
    float:left;
}

.invoice-info{
    float:right;
    text-align:right;
}

.clear{
    clear:both;
}

.section{
    margin-top:25px;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

.table th{
    background:#f4f4f4;
    padding:8px;
    border:1px solid #ddd;
}

.table td{
    padding:8px;
    border:1px solid #ddd;
}

.total-section{
    margin-top:20px;
    width:40%;
    float:right;
}

.total-table td{
    padding:6px;
}

.footer{
    margin-top:40px;
    text-align:center;
    font-size:11px;
    color:#888;
}

</style>

</head>

<body>

<div class="container">

<!-- HEADER -->

<div class="header">

<div class="company">

    @php
        $logoUrl = "https://admin-dev.famoryapp.com/assets/img/app_logo.png";
        $logoData = base64_encode(file_get_contents($logoUrl));
    @endphp
    
    <img src="data:image/png;base64,{{ $logoData }}" style="height:60px;margin-bottom:10px;">

    <h2>FamoryApp</h2>

    <p>
        123 Business Street<br>
        New York, USA<br>
        support@famoryapp.com
    </p>

</div>

<div class="invoice-info">
    <h3>INVOICE</h3>

    <p>
        <strong>Invoice No:</strong> {{ $order->invoice_no }} <br>
        <strong>Order ID:</strong> {{ $order->unique_order_id }} <br>
        <strong>Payment Mode:</strong> @if($order->payment_mode == 2) Online Payment @else Cash on Delivery @endif <br>
        <strong>Date:</strong> {{ $order->order_datetime }}
    </p>
</div>

<div class="clear"></div>

</div>

<hr>

<!-- BILLING ADDRESS -->

<!-- BILLING & SHIPPING ADDRESS -->

<div class="section">

<table width="100%" style="margin-top:20px;">
<tr>

<td width="50%" valign="top" style="padding-right:20px;">
<strong>Billing Address</strong>

<p>
{{ $order->address_data['name'] ?? '' }}<br>
{{ $order->address_data['house_number'] ?? '' }}<br>
{{ $order->address_data['road_name'] ?? '' }}<br>
{{ $order->address_data['state'] ?? '' }}<br>
{{ $order->address_data['zip_code'] ?? '' }}<br>
Phone: {{ $order->address_data['phone_number'] ?? '' }}
</p>

</td>

<td width="50%" valign="top" style="text-align:right;">
<strong>Shipping Address</strong>

<p>
{{ $order->address_data['name'] ?? '' }}<br>
{{ $order->address_data['house_number'] ?? '' }}<br>
{{ $order->address_data['road_name'] ?? '' }}<br>
{{ $order->address_data['state'] ?? '' }}<br>
{{ $order->address_data['zip_code'] ?? '' }}<br>
Phone: {{ $order->address_data['phone_number'] ?? '' }}
</p>

</td>

</tr>
</table>

</div>

<!-- PRODUCT TABLE -->

<div class="section">

<table class="table">

<thead>

<tr>
<th>#</th>
<th>Product</th>
<th>Price</th>
<th>Qty</th>
<th>Total</th>
</tr>

</thead>

<tbody>

@php
$subtotal = 0;
@endphp

@foreach($order->orderDetail as $index => $item)

@php
    $product = json_decode($item->product_json, true);
    $total = $item->buy_quantity * $item->product_unit_price;
    $subtotal += $total;
@endphp

<tr>

<td>{{ $index + 1 }}</td>

<td>
{{ $product['name'] ?? 'Product' }}
</td>

<td>
${{ number_format($item->product_unit_price, 2) }}
</td>

<td>
{{ $item->buy_quantity }}
</td>

<td>
${{ number_format($total, 2) }}
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

<!-- TOTAL -->

<div class="total-section">

<table class="total-table" width="100%">

<tr>
<td>Subtotal</td>
<td align="right">${{ number_format($order->subtotal_amount, 2) }}</td>
</tr>

<tr>
<td>Shipping</td>
<td align="right">${{ number_format($order->shipping_amount, 2) }}</td>
</tr>

<tr>
<td><strong>Total</strong></td>
<td align="right"><strong>${{ number_format($order->payable_amount, 2) }}</strong></td>
</tr>

</table>

</div>

<div class="clear"></div>

<!-- FOOTER -->

<div class="footer">

<p>
Thank you for your purchase ❤️
</p>

<p>
If you have any questions about this invoice, contact support@famoryapp.com
</p>

</div>

</div>

</body>
</html>