<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to LatestDeal</title>
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
        <h2>Welcome to LatestDeal, {{ $user->name }}! 🎉</h2>
        <p>We are thrilled to have you join our global community of smart shoppers.</p>
        <p>With LatestDeal, you get:</p>
        <ul>
            <li>🔥 Autonomous AI price tracking across top stores</li>
            <li>⚡ Instant price drop alerts directly in your inbox or browser</li>
            <li>🎫 1,200+ verified coupons and discount codes</li>
        </ul>
        @if(isset($verificationUrl))
            <p>Please confirm your email address by clicking the button below:</p>
            <a href="{{ $verificationUrl }}" class="btn">Verify My Email Address</a>
        @endif
        <div class="footer">
            <p>LatestDeal.in — Autonomous Global Deal Discovery Engine</p>
            <p>If you didn't create an account, you can safely ignore this email.</p>
        </div>
    </div>
</body>
</html>
