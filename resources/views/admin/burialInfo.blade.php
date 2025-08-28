<!DOCTYPE html>
<html>
<head>
    <title>Burial Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /*background-color: #f4f4f4;*/
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 900px;
            margin: 5px auto;
            background-color: #fff;
            padding:5px;
            border-radius: 2px;
            /*border: 2px solid gold; */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
            color: #1550AE;
        }
        .header img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
        }
        .header h1 {
            margin: 8px 0;
            font-size: 24px;
        }
        .info, .map, .notes {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .info p, .notes p {
            margin: 10px 0;
        }
        .map img {
            max-width: 100%;
            border-radius: 5px;
        }
        .img-circle {
            border-radius: 50%;
            width: 30px;
            height: 30px;
            margin: 2px;
        }
        .note {
            text-align: justify;
            text-justify: inter-word;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="margin: 3px; padding: 10px; background-color: #1550AE; color: white;">
                <h1 style="margin: 0; padding: 10px; text-align: center;">View Burial Information</h1>   
            </div>
        </div>
        <br/>
        <h2 class="section-title">Burial Details</h2>
        <!--<div class="info">-->
           
            <table>
                <tr>
                    <td><strong>Username:</strong></td>
                    <td>{{ !empty($username) ? $username : '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Funeral Home:</strong></td>
                    <td>{{ !empty($burialInfo->funeral_home) ? $burialInfo->funeral_home : '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Address:</strong></td>
                    <td>{{ !empty($burialInfo->address) ? $burialInfo->address : '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Plot Number:</strong></td>
                    <td>{{ !empty($burialInfo->plot_number) ? $burialInfo->plot_number : '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Contact:</strong></td>
                    <td>{{ !empty($burialInfo->contact) ? $burialInfo->contact : '-' }}</td>
                </tr>
            </table>
        <!--</div>-->
        <br/>
        <h2 class="section-title">Plot on Map Location</h2>
        <div class="map">
            <img src="{{ $mapImgSrc }}" alt="Map">
        </div>
        @if (!empty($burialInfo->notes))
            <h2 class="section-title">Notes</h2>
        <!--<div class="notes">-->
            <p class="note">{{ $burialInfo->notes }}</p>
        <!--</div>-->
        @endif
    </div>
</body>
</html>
