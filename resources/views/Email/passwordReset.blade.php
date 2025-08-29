<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Famory</title>
    <style>
        /* Global styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            border-radius: 15px;
            background-color: #ffffff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #1550ae;
            padding: 15px;
            text-align: center;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            color: #ffffff;
        }

        .header img {
            display: block;
            width: 100px;
            margin: 10px auto 0;
        }

        .email-container {
            padding: 20px;
            text-align: center;
        }

        .email-container h2 {
            font-size: 24px;
            margin-bottom: 10px;
            overflow: visible; 
            white-space: normal;
        }

        .email-container h3 {
            font-size: 18px;
            color: #1550ae;
            margin-top: 5px;
            overflow: visible; 
            white-space: normal;
        }

        .otp {
            font-size: 24px;
            font-weight: bold;
            color: #1550ae;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #888888;
        }
    </style>
</head>

<body>

<div class="container" style="max-width: 800px; margin: 30px auto; border-radius: 15px; background-color: #ffffff; box-shadow: 0 0 20px rgba(0, 0, 0, 0.1); border: 2px solid #d3d3d3; overflow: hidden;">
    <div class="header" style="background-color: #1550ae; padding: 15px; text-align: center; border-top-left-radius: 15px; border-top-right-radius: 15px; color: #ffffff;">
        <img src="{{ url('/') }}/assets/img/famcamlogo.png" alt="Fam Cam Logo" style="display: block; width: 100px; margin: 10px auto 0;">
    </div>
    <div class="email-container" style="padding: 20px; text-align: center;">
        <h2 style="font-size: 24px; margin-bottom: 10px; color: #333333;">Hello {{ $first_name }} {{ $last_name }},</h2>
        <h3 style="font-size: 18px; color: #1550ae; margin-top: 5px;">Email: {{ $email }}</h3>
        <p class="otp" style="font-size: 24px; font-weight: bold; color: #1550ae;">Code: {{ $token }}</p>
        <p style="font-size: 16px; color: #333333;">Your One Time Password (OTP). Please Enter The OTP to Reset Your Password.</p>
        <p style="font-size: 16px; color: #1550ae;">Thanks,<br>Famory</p>
    </div>
</div>



</body>

</html>
