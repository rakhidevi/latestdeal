<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $headline }} — LatestDeal.in</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!--[if mso]>
    <noscript><xml>
    <o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings>
    </xml></noscript>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }

        .btn-deal:hover { background-color: #dc2626 !important; box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4) !important; transform: translateY(-1px); }
        .deal-card:hover { box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12) !important; }
        .category-link:hover { background-color: #ef4444 !important; color: #ffffff !important; }

        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .content-pad { padding: 20px 16px !important; }
            .deal-cell { display: block !important; width: 100% !important; padding: 0 0 16px 0 !important; }
            .hero-title { font-size: 28px !important; line-height: 34px !important; }
            .hero-sub { font-size: 14px !important; }
            .cat-cell { padding: 4px 8px !important; font-size: 11px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; -webkit-font-smoothing: antialiased;">
    <!-- Preheader -->
    <div style="display: none; font-size: 1px; color: #f1f5f9; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        {{ $preheaderText ?? 'Handpicked savings up to 80% off — verified & live right now.' }}
    </div>

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 20px 8px;">
        <tr>
            <td align="center">
                <table role="presentation" class="email-container" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);">

                    <!-- ═══════════════════════════════════════ -->
                    <!-- HERO BANNER SECTION                    -->
                    <!-- ═══════════════════════════════════════ -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #0f172a 100%); padding: 0; position: relative;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <!-- Top accent line -->
                                <tr>
                                    <td height="5" style="background: linear-gradient(90deg, #ef4444, #f97316, #ef4444); font-size: 0; line-height: 0;">&nbsp;</td>
                                </tr>
                                <!-- Logo row -->
                                <tr>
                                    <td style="padding: 28px 32px 0 32px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td align="left">
                                                    <span style="font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px; text-decoration: none;">Latest<span style="color: #ef4444;">Deal</span><span style="color: #64748b; font-size: 14px; font-weight: 400;">.in</span></span>
                                                </td>
                                                <td align="right">
                                                    <span style="display: inline-block; background-color: #ef4444; color: #ffffff; font-size: 11px; font-weight: 800; padding: 6px 14px; border-radius: 20px; letter-spacing: 0.5px; text-transform: uppercase;">🔥 LIVE DEALS</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <!-- Hero content -->
                                <tr>
                                    <td style="padding: 36px 32px 20px 32px;" align="center">
                                        <h1 class="hero-title" style="margin: 0; font-size: 34px; font-weight: 900; color: #ffffff; letter-spacing: -1px; line-height: 40px; text-align: center;">
                                            {{ $headline }}
                                        </h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 0 32px 12px 32px;" align="center">
                                        <p class="hero-sub" style="margin: 0; font-size: 15px; color: #94a3b8; line-height: 22px; text-align: center; max-width: 440px;">
                                            {{ $subheadline }}
                                        </p>
                                    </td>
                                </tr>
                                <!-- Urgency strip -->
                                <tr>
                                    <td align="center" style="padding: 16px 32px 32px 32px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="background: linear-gradient(90deg, #dc2626, #ef4444); border-radius: 8px; padding: 10px 24px;">
                                                    <span style="font-size: 13px; font-weight: 700; color: #ffffff; letter-spacing: 0.5px;">⏰ Limited Time — Prices may change anytime!</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ═══════════════════════════════════════ -->
                    <!-- CATEGORY QUICK-LINKS NAV               -->
                    <!-- ═══════════════════════════════════════ -->
                    <tr>
                        <td style="padding: 20px 24px 8px 24px; background-color: #ffffff;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="4">
                                            <tr>
                                                <td class="cat-cell category-link" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px;">
                                                    <a href="{{ url('/deals?category=electronics') }}" style="font-size: 12px; font-weight: 700; color: #334155; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;">📱 Electronics</a>
                                                </td>
                                                <td class="cat-cell category-link" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px;">
                                                    <a href="{{ url('/deals?category=fashion') }}" style="font-size: 12px; font-weight: 700; color: #334155; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;">👗 Fashion</a>
                                                </td>
                                                <td class="cat-cell category-link" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px;">
                                                    <a href="{{ url('/deals?category=home') }}" style="font-size: 12px; font-weight: 700; color: #334155; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;">🏠 Home</a>
                                                </td>
                                                <td class="cat-cell category-link" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px;">
                                                    <a href="{{ url('/deals?category=beauty') }}" style="font-size: 12px; font-weight: 700; color: #334155; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;">✨ Beauty</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ═══════════════════════════════════════ -->
                    <!-- DEAL CARDS GRID (2-column)             -->
                    <!-- ═══════════════════════════════════════ -->
                    <tr>
                        <td class="content-pad" style="padding: 20px 24px 12px 24px;">

                            @foreach($deals->chunk(2) as $pair)
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 16px;">
                                <tr>
                                    @foreach($pair as $deal)
                                    <td class="deal-cell deal-card" width="50%" valign="top" style="padding: {{ $loop->first ? '0 8px 0 0' : '0 0 0 8px' }};">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                                            <!-- Product Image -->
                                            <tr>
                                                <td align="center" style="background-color: #f8fafc; padding: 16px 12px; position: relative;">
                                                    <!-- Discount Badge -->
                                                    @if($deal->discount_percentage > 0)
                                                    <div style="position: absolute; top: 8px; left: 8px; z-index: 2;">
                                                        <span style="background: linear-gradient(135deg, #dc2626, #ef4444); color: #ffffff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 6px; display: inline-block;">
                                                            {{ round($deal->discount_percentage) }}% OFF
                                                        </span>
                                                    </div>
                                                    @endif
                                                    <a href="{{ url('/deal/' . $deal->slug . '/' . $deal->hash_id) }}" target="_blank" style="text-decoration: none;">
                                                        <img src="{{ $deal->image_url }}" alt="{{ $deal->title }}" width="220" style="display: block; width: 100%; max-width: 220px; height: 140px; object-fit: contain; border-radius: 8px;">
                                                    </a>
                                                </td>
                                            </tr>
                                            <!-- Deal Info -->
                                            <tr>
                                                <td style="padding: 14px 14px 6px 14px;">
                                                    <!-- Brand -->
                                                    @if($deal->brandRelation)
                                                    <p style="margin: 0 0 4px 0; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">{{ $deal->brandRelation->name }}</p>
                                                    @endif
                                                    <!-- Title -->
                                                    <a href="{{ url('/deal/' . $deal->slug . '/' . $deal->hash_id) }}" target="_blank" style="text-decoration: none;">
                                                        <p style="margin: 0; font-size: 13px; font-weight: 600; color: #0f172a; line-height: 18px; max-height: 36px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $deal->title }}</p>
                                                    </a>
                                                </td>
                                            </tr>
                                            <!-- Pricing Row -->
                                            <tr>
                                                <td style="padding: 8px 14px 4px 14px;">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td>
                                                                <span style="font-size: 20px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">₹{{ number_format($deal->discounted_price ?? $deal->effective_price ?? 0) }}</span>
                                                            </td>
                                                        </tr>
                                                        @if($deal->original_price && $deal->original_price > ($deal->discounted_price ?? 0))
                                                        <tr>
                                                            <td>
                                                                <span style="font-size: 12px; color: #94a3b8; text-decoration: line-through;">MRP ₹{{ number_format($deal->original_price) }}</span>
                                                                <span style="font-size: 12px; font-weight: 700; color: #16a34a; margin-left: 4px;">Save ₹{{ number_format($deal->amount_saved ?? ($deal->original_price - ($deal->discounted_price ?? 0))) }}</span>
                                                            </td>
                                                        </tr>
                                                        @endif
                                                    </table>
                                                </td>
                                            </tr>
                                            <!-- CTA Button -->
                                            <tr>
                                                <td style="padding: 12px 14px 16px 14px;">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td align="center" style="border-radius: 8px; background-color: #ef4444; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);">
                                                                <a href="{{ $deal->affiliate_url }}" target="_blank" class="btn-deal" style="font-size: 13px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 10px 16px; display: block; text-align: center; border: 1px solid #ef4444;">
                                                                    Grab Deal →
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    @endforeach

                                    {{-- Spacer cell when odd number of deals --}}
                                    @if($pair->count() === 1)
                                    <td class="deal-cell" width="50%" style="padding: 0 0 0 8px;">&nbsp;</td>
                                    @endif
                                </tr>
                            </table>
                            @endforeach

                        </td>
                    </tr>

                    <!-- ═══════════════════════════════════════ -->
                    <!-- VIEW ALL DEALS CTA                     -->
                    <!-- ═══════════════════════════════════════ -->
                    <tr>
                        <td align="center" style="padding: 8px 24px 32px 24px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="border-radius: 12px; background: linear-gradient(135deg, #0f172a, #1e293b); box-shadow: 0 4px 16px rgba(15, 23, 42, 0.2);">
                                        <a href="{{ url('/') }}" target="_blank" style="font-size: 16px; font-weight: 800; color: #ffffff; text-decoration: none; border-radius: 12px; padding: 16px 48px; display: inline-block; border: 1px solid #334155; letter-spacing: 0.3px;">
                                            View All Deals →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ═══════════════════════════════════════ -->
                    <!-- VALUE PROP STRIP                       -->
                    <!-- ═══════════════════════════════════════ -->
                    <tr>
                        <td style="padding: 0 24px 24px 24px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: linear-gradient(135deg, #fef2f2, #fff7ed); border-radius: 12px; border: 1px solid #fee2e2;">
                                <tr>
                                    <td align="center" width="33%" style="padding: 20px 8px;">
                                        <div style="font-size: 24px; margin-bottom: 6px;">🔍</div>
                                        <div style="font-size: 13px; font-weight: 800; color: #0f172a;">AI-Verified</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Every deal is checked</div>
                                    </td>
                                    <td align="center" width="33%" style="padding: 20px 8px; border-left: 1px solid #fecaca; border-right: 1px solid #fecaca;">
                                        <div style="font-size: 24px; margin-bottom: 6px;">⚡</div>
                                        <div style="font-size: 13px; font-weight: 800; color: #0f172a;">Real-Time Prices</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Prices tracked 24/7</div>
                                    </td>
                                    <td align="center" width="33%" style="padding: 20px 8px;">
                                        <div style="font-size: 24px; margin-bottom: 6px;">💸</div>
                                        <div style="font-size: 13px; font-weight: 800; color: #0f172a;">Lowest Price</div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Guaranteed savings</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ═══════════════════════════════════════ -->
                    <!-- DARK FOOTER                            -->
                    <!-- ═══════════════════════════════════════ -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 32px 32px 24px 32px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <!-- Brand -->
                                <tr>
                                    <td align="center" style="padding-bottom: 16px;">
                                        <span style="font-size: 20px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px;">Latest<span style="color: #ef4444;">Deal</span><span style="color: #64748b; font-size: 13px; font-weight: 400;">.in</span></span>
                                        <p style="margin: 6px 0 0 0; font-size: 12px; color: #64748b;">Autonomous Deal Discovery & Price Tracking Engine</p>
                                    </td>
                                </tr>
                                <!-- Divider -->
                                <tr>
                                    <td style="border-bottom: 1px solid #1e293b; padding-bottom: 16px;">&nbsp;</td>
                                </tr>
                                <!-- Nav Links -->
                                <tr>
                                    <td align="center" style="padding: 16px 0;">
                                        <a href="{{ url('/') }}" style="color: #cbd5e1; text-decoration: none; font-size: 12px; font-weight: 600; padding: 0 8px;">Website</a>
                                        <span style="color: #334155;">•</span>
                                        <a href="{{ url('/terms') }}" style="color: #cbd5e1; text-decoration: none; font-size: 12px; font-weight: 600; padding: 0 8px;">Terms</a>
                                        <span style="color: #334155;">•</span>
                                        <a href="{{ url('/privacy') }}" style="color: #cbd5e1; text-decoration: none; font-size: 12px; font-weight: 600; padding: 0 8px;">Privacy</a>
                                    </td>
                                </tr>
                                <!-- Unsubscribe -->
                                @if(isset($unsubscribeUrl))
                                <tr>
                                    <td align="center" style="padding-top: 8px;">
                                        <p style="margin: 0; font-size: 12px; color: #475569;">
                                            Don't want deal alerts? <a href="{{ $unsubscribeUrl }}" style="color: #ef4444; text-decoration: underline; font-weight: 600;">Unsubscribe</a>
                                        </p>
                                    </td>
                                </tr>
                                @endif
                                <!-- Copyright -->
                                <tr>
                                    <td align="center" style="padding-top: 16px;">
                                        <p style="margin: 0; font-size: 11px; color: #475569;">&copy; {{ date('Y') }} LatestDeal.in. All rights reserved.</p>
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
