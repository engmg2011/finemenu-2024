<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        .otp-code {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            margin: 20px 0;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>OTP Verification</h1>
    </div>
    <div class="content">
        <p>Hello,</p>
        <p>Your verification code is:</p>
        <div class="otp-code">
            {{ $otp }}
        </div>
        <p>This code will expire in {{ $expires ?? '5' }} minutes.</p>
        <p>If you didn't request this code, please ignore this email.</p>
    </div>
    <div class="footer">
        <p>This is an automated message, please do not reply.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</div>
</body>
</html>
