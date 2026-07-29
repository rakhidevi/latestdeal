<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'LatestDeal.in — Global Deal Discovery')</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Reset styles for cross-client compatibility */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0f172a; }
        
        /* Interactive CTA button hover */
        .btn-primary:hover {
            background-color: #dc2626 !important;
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4) !important;
        }

        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; margin: 0 !important; }
            .content-padding { padding: 24px 20px !important; }
            .hero-title { font-size: 22px !important; line-height: 28px !important; }
            .feature-grid { display: block !important; width: 100% !important; margin-bottom: 12px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; -webkit-font-smoothing: antialiased;">
    <!-- Preheader Text (Hidden preview in inbox) -->
    <div style="display: none; font-size: 1px; color: #f1f5f9; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        @yield('preheader', 'LatestDeal.in — Handpicked verified deals & instant price drop alerts.')
    </div>

    <!-- Main Outer Wrapper -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 32px 12px;">
        <tr>
            <td align="center">
                <!-- Main Email Card Container -->
                <table role="presentation" class="email-container" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);">
                    
                    <!-- Top Vibrant Brand Accent Bar -->
                    <tr>
                        <td height="6" style="background: linear-gradient(90deg, #dc2626 0%, #ef4444 50%, #f97316 100%); font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>

                    <!-- Header with Logo -->
                    <tr>
                        <td align="center" style="padding: 32px 32px 20px 32px; border-bottom: 1px solid #f1f5f9;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="left">
                                        <a href="{{ url('/') }}" target="_blank" style="text-decoration: none;">
                                            <img src="https://staging.latestdeal.in/images/logo.png" alt="LatestDeal.in" width="160" style="display: block; width: 160px; max-width: 160px; height: auto;">
                                        </a>
                                    </td>
                                    <td align="right" style="font-size: 12px; font-weight: 600; color: #64748b; letter-spacing: 0.5px; text-transform: uppercase;">
                                        Verified Deals & Alerts
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Dynamic Body Content -->
                    <tr>
                        <td class="content-padding" style="padding: 36px 40px 32px 40px;">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Enterprise Value Proposition Banner (3-Feature Cards) -->
                    <tr>
                        <td style="padding: 0 40px 32px 40px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #f1f5f9;">
                                <tr>
                                    <td class="feature-grid" align="center" width="33%" style="padding: 8px;">
                                        <div style="font-size: 20px; margin-bottom: 4px;">🔥</div>
                                        <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Top Discounts</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Curated & Live</div>
                                    </td>
                                    <td class="feature-grid" align="center" width="33%" style="padding: 8px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                                        <div style="font-size: 20px; margin-bottom: 4px;">⚡</div>
                                        <div style="font-size: 13px; font-weight: 700; color: #0f172a;">Price Drops</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Instant Notifications</div>
                                    </td>
                                    <td class="feature-grid" align="center" width="33%" style="padding: 8px;">
                                        <div style="font-size: 20px; margin-bottom: 4px;">🛡️</div>
                                        <div style="font-size: 13px; font-weight: 700; color: #0f172a;">100% Verified</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Guaranteed Savings</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 32px 40px; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px; color: #94a3b8; font-size: 12px; line-height: 18px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="left" style="padding-bottom: 16px; border-bottom: 1px solid #1e293b;">
                                        <span style="font-size: 14px; font-weight: 800; color: #ffffff; letter-spacing: -0.2px;">Latest<span style="color: #ef4444;">Deal</span>.in</span>
                                        <span style="display: block; font-size: 12px; color: #64748b; margin-top: 4px;">Autonomous Global Deal Discovery & Price Tracking Engine</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" style="padding-top: 16px;">
                                        <p style="margin: 0 0 8px 0; color: #64748b;">
                                            You are receiving this automated email because of your account activity or alert subscription on LatestDeal.in.
                                        </p>
                                        <p style="margin: 0 0 12px 0;">
                                            <a href="{{ url('/') }}" style="color: #cbd5e1; text-decoration: none; font-weight: 600;">Visit Website</a> &nbsp;&bull;&nbsp;
                                            <a href="{{ url('/terms') }}" style="color: #cbd5e1; text-decoration: none; font-weight: 600;">Terms of Service</a> &nbsp;&bull;&nbsp;
                                            <a href="{{ url('/privacy') }}" style="color: #cbd5e1; text-decoration: none; font-weight: 600;">Privacy Policy</a>
                                        </p>
                                        @if(isset($unsubscribeUrl))
                                            <p style="margin: 0; color: #64748b;">
                                                Don't want to receive promotional deal alerts? <a href="{{ $unsubscribeUrl }}" style="color: #ef4444; text-decoration: underline;">Unsubscribe from alerts</a>.
                                            </p>
                                        @endif
                                        <p style="margin: 16px 0 0 0; font-size: 11px; color: #475569;">
                                            &copy; {{ date('Y') }} LatestDeal.in. All rights reserved. Registered automated system.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
