<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Famory</title>
    @include('layouts.partials.head')
</head>

<body style="font-family: Arial, sans-serif;">

    <div
        style="max-width:800px; margin: 30px auto; border-radius: 15px; background-color: #ffffff;">
        <div class="card border-0 mb-3" style="background-color: #ffffff;">
            <div style="background:#ffffff; padding: 10px; text-align: center;">
                <img src="{{ url('/') }}/assets/img/fam-cam-logo.png" alt="" style="display: block; width: 180px; height: 172px; margin: 1px auto 0">
            </div>
            <div class="card-body">
                <p style="color: #333333; font-size: 16px;"><strong>Hello,  {{ $data->user->first_name }} {{ $data->user->last_name }}</strong></p>
                <p style="color: #333333; font-size: 16px;">
                    We are pleased to inform you that your order {{ $data->order_id }} has been shipped and is on its way to you!
                </p>
                <p style="color: #333333; font-size: 16px;">
                    Here are the details of your order:
                </p>
                <ul>
                    <li><p style="color: #333333;"><strong>Order ID:</strong> {{ $data->order_id }}</p></li>
                    <li><p style="color: #333333;"><strong>Product Name:</strong> {{ $data->product->name }}</p></li>
                    <li><p style="color: #333333;"><strong>Quantity:</strong> {{ $data->quantity }}</p></li>
                </ul>
                <p style="color: #333333; font-size: 16px;">
                    Your Order Tracking Id is : <span style="font-weight:700;">{{ $shipTrackingId }}</span> 
                </p>
                <p style="color: #333333; font-size: 16px;">
                    If you have any questions or need further assistance, feel free to reach out to us. We're here to help you with anything you need.
                </p>
                <br/>
                <strong style="color: #0d397f; font-size: 16px;">Best regards,<br>Famory</strong>
                <br/>
                <br/>
            </div>
        </div>
    </div>

</body>

</html>
