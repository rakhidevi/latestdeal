@extends('emails.master')

@section('title', 'Reset Your Password — LatestDeal.in')

@section('preheader', 'Follow the instructions in this email to securely reset your account password.')

@section('content')
<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
    <!-- Hero Icon Badge -->
    <tr>
        <td align="left" style="padding-bottom: 24px;">
            <div style="display: inline-block; background-color: #f3e8ff; border: 1px solid #e9d5ff; border-radius: 12px; padding: 12px 16px;">
                <span style="font-size: 28px; line-height: 28px; display: block;">🔑</span>
            </div>
        </td>
    </tr>

    <!-- Title -->
    <tr>
        <td align="left" style="padding-bottom: 16px;">
            <h1 class="hero-title" style="margin: 0; font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; line-height: 32px;">
                Reset Password Request
            </h1>
        </td>
    </tr>

    <!-- Message Body -->
    <tr>
        <td align="left" style="padding-bottom: 28px; font-size: 15px; line-height: 24px; color: #334155;">
            <p style="margin: 0 0 16px 0;">
                Hello {{ $user->name ?? 'Shopper' }},
            </p>
            <p style="margin: 0 0 16px 0;">
                We received a request to reset your password for your <strong>LatestDeal.in</strong> account. Click the button below to choose a new password.
            </p>

            <div style="background-color: #f8fafc; border-left: 4px solid #9333ea; border-radius: 4px 8px 8px 4px; padding: 16px; margin: 20px 0; font-size: 14px; color: #475569;">
                <strong>⏱️ Link Expiry:</strong> For security reasons, this password reset link will expire in <strong>60 minutes</strong>. If you did not request a password reset, please ignore this email or contact support.
            </div>
        </td>
    </tr>

    <!-- Bulletproof CTA Button -->
    <tr>
        <td align="left" style="padding-bottom: 32px;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center" style="border-radius: 10px; background-color: #ef4444; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);">
                        <a href="{{ $resetUrl }}" target="_blank" class="btn-primary" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 10px; padding: 14px 28px; display: inline-block; border: 1px solid #ef4444;">
                            Reset Password &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <!-- Alternative Link Fallback -->
    <tr>
        <td align="left" style="font-size: 12px; color: #64748b; line-height: 18px; padding-top: 12px; border-top: 1px dashed #e2e8f0;">
            <p style="margin: 0 0 6px 0;">If the button above does not work, copy and paste this URL into your browser:</p>
            <p style="margin: 0; word-break: break-all; color: #0284c7; font-family: monospace;">{{ $resetUrl }}</p>
        </td>
    </tr>
</table>
@endsection
