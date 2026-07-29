@extends('emails.master')

@section('title', 'Welcome to LatestDeal.in')

@section('preheader', 'Your account has been created. Start discovering verified deals and saving big today!')

@section('content')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <!-- Hero Icon Badge -->
    <tr>
        <td align="left" style="padding-bottom: 24px;">
            <div style="display: inline-block; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 12px 16px;">
                <span style="font-size: 28px; line-height: 28px; display: block;">🛍️</span>
            </div>
        </td>
    </tr>

    <!-- Title -->
    <tr>
        <td align="left" style="padding-bottom: 16px;">
            <h1 class="hero-title" style="margin: 0; font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; line-height: 32px;">
                Welcome to LatestDeal, {{ $user->name ?? 'Shopper' }}! 👋
            </h1>
        </td>
    </tr>

    <!-- Message Body -->
    <tr>
        <td align="left" style="padding-bottom: 28px; font-size: 15px; line-height: 24px; color: #334155;">
            <p style="margin: 0 0 16px 0;">
                Your account is ready! You are now part of India's fastest-growing deal discovery community.
            </p>
            <p style="margin: 0 0 16px 0;">
                Here is what you can do with your new account:
            </p>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 20px 0;">
                <tr>
                    <td width="28" valign="top" style="font-size: 16px;">🎯</td>
                    <td style="padding-left: 8px; padding-bottom: 12px; font-size: 14px; color: #334155;">
                        <strong>Set Custom Price Drop Alerts:</strong> Track your favorite gadgets and apparel.
                    </td>
                </tr>
                <tr>
                    <td width="28" valign="top" style="font-size: 16px;">⭐</td>
                    <td style="padding-left: 8px; padding-bottom: 12px; font-size: 14px; color: #334155;">
                        <strong>Save Favorites to Wishlist:</strong> Access your saved deals across all devices.
                    </td>
                </tr>
                <tr>
                    <td width="28" valign="top" style="font-size: 16px;">💬</td>
                    <td style="padding-left: 8px; font-size: 14px; color: #334155;">
                        <strong>Community Upvoting:</strong> Upvote verified deals and help fellow shoppers save.
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Bulletproof CTA Button -->
    <tr>
        <td align="left" style="padding-bottom: 32px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="border-radius: 10px; background-color: #ef4444; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);">
                        <a href="{{ url('/login') }}" target="_blank" class="btn-primary" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 10px; padding: 14px 28px; display: inline-block; border: 1px solid #ef4444;">
                            Go to Your Dashboard &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
