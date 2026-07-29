@extends('emails.master')

@section('title', 'Subscription Confirmed — LatestDeal Alerts')

@section('preheader', 'You are now subscribed to LatestDeal handpicked discounts and instant price drop alerts!')

@section('content')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <!-- Hero Icon Badge -->
    <tr>
        <td align="left" style="padding-bottom: 24px;">
            <div style="display: inline-block; background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 12px; padding: 12px 16px;">
                <span style="font-size: 28px; line-height: 28px; display: block;">🔔</span>
            </div>
        </td>
    </tr>

    <!-- Title -->
    <tr>
        <td align="left" style="padding-bottom: 16px;">
            <h1 class="hero-title" style="margin: 0; font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; line-height: 32px;">
                Subscription Confirmed! 🎉
            </h1>
        </td>
    </tr>

    <!-- Message Body -->
    <tr>
        <td align="left" style="padding-bottom: 28px; font-size: 15px; line-height: 24px; color: #334155;">
            <p style="margin: 0 0 16px 0;">
                Thank you for subscribing to <strong>LatestDeal.in</strong> price drop alerts and daily deal notifications.
            </p>
            <p style="margin: 0 0 16px 0;">
                Our autonomous discovery engine scans e-commerce platforms 24/7 so you never miss out on verified discounts, price drops, or limited-time promotional codes.
            </p>
            <div style="background-color: #f8fafc; border-left: 4px solid #ef4444; border-radius: 4px 8px 8px 4px; padding: 16px; margin: 20px 0; font-size: 14px; color: #475569;">
                <strong style="color: #0f172a;">⚡ What to expect next:</strong> You'll receive real-time notifications whenever high-value deals matching popular categories drop to historical lows.
            </div>
        </td>
    </tr>

    <!-- Bulletproof CTA Button -->
    <tr>
        <td align="left" style="padding-bottom: 32px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="border-radius: 10px; background-color: #ef4444; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);">
                        <a href="{{ url('/') }}" target="_blank" class="btn-primary" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 10px; padding: 14px 28px; display: inline-block; border: 1px solid #ef4444;">
                            Explore Top Deals Now &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection
