<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
        }
        .button {
            background-color: #3490dc;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            font-weight: bold;
            margin: 10px 0;
        }
        .footer {
            font-size: 12px;
            color: #777;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hello {{ $user->name }},</h1>
        <p>Thank you for registering with us! To complete your registration, please verify your email address by clicking the button below.</p>
        <a href="{{ $verificationUrl }}" class="button" style="background-color: #28a745;">Verify Your Account</a>
        <p>If you did not register for an account, no further action is required.</p>
        <p class="footer">Best regards,<br>The {{ config('app.name') }} Team</p>
    </div>
</body>
</html>
