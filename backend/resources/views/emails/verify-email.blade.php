<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify Email Address</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e2e8f0; }
        .logo { max-height: 48px; margin-bottom: 24px; }
        .btn { display: inline-block; background-color: #ef4444; color: #ffffff !important; padding: 12px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; margin-top: 20px; }
        .footer { margin-top: 32px; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ asset('/images/logo.png') }}" alt="LatestDeal" class="logo">
        <h2>Verify Your Email Address</h2>
        <p>Hi {{ $user->name }},</p>
        <p>Please verify your email address to activate full access to price drop alerts and saved deals.</p>
        <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
        <p style="margin-top: 24px; font-size: 13px; color: #64748b;">This link will expire in 60 minutes.</p>
        <div class="footer">
            <p>LatestDeal.in — Autonomous Global Deal Discovery Engine</p>
        </div>
    </div>
</body>
</html>
