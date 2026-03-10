<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

body{
    font-family: Arial, Helvetica, sans-serif;
    background:#f5f5f5;
    padding:20px;
}

.container{
    max-width:650px;
    background:#ffffff;
    margin:auto;
    padding:30px;
    border-radius:6px;
}

.header{
    text-align:center;
    border-bottom:1px solid #eee;
    padding-bottom:20px;
}

.logo{
    height:50px;
}

.title{
    font-size:22px;
    margin-top:10px;
    color:#333;
}

.section{
    margin-top:25px;
}

.order-box{
    background:#fafafa;
    padding:15px;
    border-radius:5px;
}

.table{
    width:100%;
    border-collapse:collapse;
    margin-top:15px;
}

.table th{
    background:#f3f3f3;
    padding:10px;
    border:1px solid #ddd;
    font-size:13px;
}

.table td{
    padding:10px;
    border:1px solid #ddd;
    font-size:13px;
}

.total{
    margin-top:20px;
    width:40%;
    float:right;
}

.total td{
    padding:6px;
}

.footer{
    text-align:center;
    margin-top:40px;
    font-size:12px;
    color:#777;
}

</style>

</head>

<body>

<div class="container">

<!-- HEADER -->

<div class="header">

<img src="https://admin-dev.famoryapp.com/assets/img/app_logo.png" class="logo">

<div class="title">
Order Confirmation
</div>

</div>

<!-- MESSAGE -->

<div class="section">

<p>Hello <strong>{{ $order->user->first_name }}</strong>,</p>

<p>
Thank you for your order! Your purchase has been confirmed.
</p>

</div>

<!-- ORDER INFO -->

<div class="section order-box">

<strong>Order Details</strong>

<p>
Order ID: <strong>{{ $order->unique_order_id }}</strong><br>
Invoice No: <strong>{{ $order->invoice_no }}</strong><br>
Date: {{ $order->order_datetime }}
</p>

</div>

<!-- SHIPPING ADDRESS -->

<!-- <div class="section">

<strong>Shipping Address</strong>

<p>
{{ $order->address_data['name'] ?? '' }}<br>
{{ $order->address_data['house_number'] ?? '' }}<br>
{{ $order->address_data['road_name'] ?? '' }}<br>
{{ $order->address_data['state'] ?? '' }} {{ $order->address_data['zip_code'] ?? '' }}<br>
Phone: {{ $order->address_data['phone_number'] ?? '' }}
</p>

</div> -->

<!-- ORDER ITEMS -->

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

@php $subtotal = 0; @endphp

@foreach($order->orderDetail as $index => $item)

@php
$product = json_decode($item->product_json,true);
$total = $item->product_unit_price * $item->buy_quantity;
$subtotal += $total;
@endphp

<tr>

<td>{{ $index+1 }}</td>

<td>
{{ $product['name'] ?? 'Product' }}
</td>

<td>
${{ number_format($item->product_unit_price,2) }}
</td>

<td>
{{ $item->buy_quantity }}
</td>

<td>
${{ number_format($total,2) }}
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

<!-- TOTAL -->

<div class="total">

<table width="100%">

<tr>
<td>Subtotal</td>
<td align="right">${{ number_format($order->subtotal_amount,2) }}</td>
</tr>

<tr>
<td>Shipping</td>
<td align="right">${{ number_format($order->shipping_amount,2) }}</td>
</tr>

<tr>
<td><strong>Total</strong></td>
<td align="right"><strong>${{ number_format($order->payable_amount,2) }}</strong></td>
</tr>

</table>

</div>

<div style="clear:both"></div>

<!-- FOOTER -->

<div class="footer">

<p>
Your invoice is attached with this email.
</p>

<p>
If you have any questions, contact us at <br>
support@famoryapp.com
</p>

<p>
© {{ date('Y') }} FamoryApp. All rights reserved.
</p>

</div>

</div>

</body>
</html>