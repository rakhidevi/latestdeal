<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $headline }} — LatestDeal.in</title>
    <!--[if mso]>
    <noscript><xml>
    <o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings>
    </xml></noscript>
    <![endif]-->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #0f172a; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        a { color: inherit; }

        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .deal-cell { display: block !important; width: 100% !important; padding: 0 0 12px 0 !important; }
            .hero-title { font-size: 32px !important; line-height: 38px !important; }
            .hero-sub { font-size: 15px !important; }
            .cat-cell { display: inline-block !important; margin-bottom: 6px !important; }
            .mob-pad { padding: 16px 12px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0f172a; -webkit-font-smoothing: antialiased;">
    <!-- Preheader text (hidden but shows in Gmail preview) -->
    <div style="display: none; font-size: 1px; color: #0f172a; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;">
        {{ $preheaderText ?? '🔥 Up to 80% off — handpicked deals, verified & live right now!' }}
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>

    <!-- OUTER WRAPPER — dark background -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a;">
        <tr>
            <td align="center" style="padding: 16px 8px;">

                <!-- EMAIL CONTAINER -->
                <table role="presentation" class="email-container" border="0" cellpadding="0" cellspacing="0" width="600" style="max-width: 600px; width: 100%; overflow: hidden;">

                    <!-- ════════════════════════════════════════════════ -->
                    <!-- TOP BAR: Logo + Live Badge                      -->
                    <!-- ════════════════════════════════════════════════ -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 20px 24px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="left" valign="middle">
                                        <a href="{{ url('/') }}" style="text-decoration: none;">
                                            <span style="font-size: 24px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px;">Latest<span style="color: #ef4444;">Deal</span></span><span style="color: #475569; font-size: 13px;">.in</span>
                                        </a>
                                    </td>
                                    <td align="right" valign="middle">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="width: 8px; height: 8px; background-color: #22c55e; border-radius: 50%; margin-right: 6px;">&nbsp;</td>
                                                <td style="padding-left: 6px;">
                                                    <span style="font-size: 11px; font-weight: 700; color: #22c55e; text-transform: uppercase; letter-spacing: 1px;">LIVE NOW</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ════════════════════════════════════════════════ -->
                    <!-- HERO BANNER IMAGE (Full-width)                  -->
                    <!-- ════════════════════════════════════════════════ -->
                    <tr>
                        <td style="padding: 0;">
                            <a href="{{ url('/') }}" target="_blank" style="text-decoration: none;">
                                <img src="{{ asset('images/email-hero-banner.png') }}" alt="{{ $headline }}" width="600" style="display: block; width: 100%; max-width: 600px; height: auto; border-radius: 12px 12px 0 0;" />
                            </a>
                        </td>
                    </tr>

                    <!-- ════════════════════════════════════════════════ -->
                    <!-- URGENCY TICKER STRIP                            -->
                    <!-- ════════════════════════════════════════════════ -->
                    <tr>
                        <td style="background: linear-gradient(90deg, #dc2626, #ef4444, #f97316, #ef4444, #dc2626); padding: 12px 24px; text-align: center;">
                            <span style="font-size: 13px; font-weight: 800; color: #ffffff; letter-spacing: 1px; text-transform: uppercase;">⚡ PRICES DROP EVERY HOUR — GRAB BEFORE THEY'RE GONE ⚡</span>
                        </td>
                    </tr>

                    <!-- ════════════════════════════════════════════════ -->
                    <!-- CATEGORY NAVIGATION                             -->
                    <!-- ════════════════════════════════════════════════ -->
                    <tr>
                        <td style="background-color: #1e293b; padding: 16px 24px; text-align: center;">
                            <!--[if mso]><table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center"><tr><![endif]-->
                            @php
                                $categories = [
                                    ['📱', 'Electronics', 'electronics'],
                                    ['👗', 'Fashion', 'fashion'],
                                    ['🏠', 'Home', 'home'],
                                    ['✨', 'Beauty', 'beauty'],
                                    ['🎮', 'Gaming', 'gaming'],
                                ];
                            @endphp
                            @foreach($categories as $cat)
                            <!--[if mso]><td valign="top"><![endif]-->
                            <a href="{{ url('/categories/' . $cat[2]) }}" class="cat-cell" style="display: inline-block; padding: 8px 16px; margin: 3px; background-color: #334155; border-radius: 20px; text-decoration: none; font-size: 12px; font-weight: 700; color: #e2e8f0; letter-spacing: 0.3px;">{{ $cat[0] }} {{ $cat[1] }}</a>
                            <!--[if mso]></td><![endif]-->
                            @endforeach
                            <!--[if mso]></tr></table><![endif]-->
                        </td>
                    </tr>

                    <!-- ════════════════════════════════════════════════ -->
                    <!-- SECTION HEADER: Today's Picks                   -->
                    <!-- ════════════════════════════════════════════════ -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 28px 24px 16px 24px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <h2 style="margin: 0; font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: -0.3px;">🎯 Today's Top Picks</h2>
                                        <p style="margin: 6px 0 0 0; font-size: 13px; color: #94a3b8;">Curated by our AI — verified lowest prices across India</p>
                                    </td>
                                    <td align="right" valign="bottom">
                                        <a href="{{ url('/') }}" style="font-size: 12px; font-weight: 700; color: #ef4444; text-decoration: none;">View All →</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ════════════════════════════════════════════════ -->
                    <!-- DEAL CARDS (2-column grid)                      -->
                    <!-- ════════════════════════════════════════════════ -->
                    @foreach($deals->chunk(2) as $pairIndex => $pair)
                    <tr>
                        <td class="mob-pad" style="background-color: #0f172a; padding: 0 24px 16px 24px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    @foreach($pair as $deal)
                                    <td class="deal-cell" width="50%" valign="top" style="padding: {{ $loop->first ? '0 6px 0 0' : '0 0 0 6px' }};">
                                        <!-- DEAL CARD -->
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #1e293b; border-radius: 12px; overflow: hidden; border: 1px solid #334155;">

                                            <!-- Product Image with Discount Badge overlay -->
                                            <tr>
                                                <td style="position: relative; padding: 0;">
                                                    <a href="{{ url('/deal/' . $deal->slug . '/' . $deal->hash_id) }}" target="_blank" style="text-decoration: none;">
                                                        <img src="{{ $deal->image_url }}" alt="{{ $deal->title }}" width="264" style="display: block; width: 100%; height: 160px; object-fit: cover; border-radius: 12px 12px 0 0;" />
                                                    </a>
                                                    <!-- Discount Badge — positioned absolutely -->
                                                    @if($deal->discount_percentage > 0)
                                                    <!--[if !mso]><!-->
                                                    <div style="position: absolute; top: 10px; right: 10px; background: linear-gradient(135deg, #dc2626, #b91c1c); color: #ffffff; font-size: 14px; font-weight: 900; padding: 6px 10px; border-radius: 8px; line-height: 1; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                                                        {{ round($deal->discount_percentage) }}%<br><span style="font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">OFF</span>
                                                    </div>
                                                    <!--<![endif]-->
                                                    @endif
                                                </td>
                                            </tr>

                                            <!-- Deal Info -->
                                            <tr>
                                                <td style="padding: 14px 14px 8px 14px;">
                                                    @if($deal->brandRelation)
                                                    <p style="margin: 0 0 4px 0; font-size: 10px; font-weight: 800; color: #ef4444; text-transform: uppercase; letter-spacing: 1px;">{{ $deal->brandRelation->name }}</p>
                                                    @elseif($deal->merchant)
                                                    <p style="margin: 0 0 4px 0; font-size: 10px; font-weight: 800; color: #38bdf8; text-transform: uppercase; letter-spacing: 1px;">{{ $deal->merchant->name }}</p>
                                                    @endif
                                                    <a href="{{ url('/deal/' . $deal->slug . '/' . $deal->hash_id) }}" target="_blank" style="text-decoration: none;">
                                                        <p style="margin: 0; font-size: 13px; font-weight: 600; color: #f1f5f9; line-height: 18px; max-height: 36px; overflow: hidden;">{{ \Illuminate\Support\Str::limit($deal->title, 50) }}</p>
                                                    </a>
                                                </td>
                                            </tr>

                                            <!-- Pricing -->
                                            <tr>
                                                <td style="padding: 4px 14px 6px 14px;">
                                                    <span style="font-size: 22px; font-weight: 900; color: #22c55e;">₹{{ number_format($deal->discounted_price ?? $deal->effective_price ?? 0) }}</span>
                                                    @if($deal->original_price && $deal->original_price > ($deal->discounted_price ?? 0))
                                                    <br>
                                                    <span style="font-size: 12px; color: #64748b; text-decoration: line-through;">₹{{ number_format($deal->original_price) }}</span>
                                                    <span style="font-size: 11px; font-weight: 700; color: #fbbf24; margin-left: 4px;">Save ₹{{ number_format($deal->amount_saved ?? ($deal->original_price - ($deal->discounted_price ?? 0))) }}</span>
                                                    @endif
                                                </td>
                                            </tr>

                                            <!-- CTA Button -->
                                            <tr>
                                                <td style="padding: 8px 14px 16px 14px;">
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td align="center" style="border-radius: 8px; background: linear-gradient(135deg, #ef4444, #dc2626);">
                                                                <a href="{{ $deal->affiliate_url }}" target="_blank" style="font-size: 13px; font-weight: 800; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 11px 0; display: block; text-align: center; letter-spacing: 0.5px;">
                                                                    🛒 GRAB DEAL
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    @endforeach

                                    @if($pair->count() === 1)
                                    <td class="deal-cell" width="50%" style="padding: 0 0 0 6px;">&nbsp;</td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    <!-- ════════════════════════════════════════════════ -->
                    <!-- VIEW ALL DEALS — Big Bold CTA                   -->
                    <!-- ════════════════════════════════════════════════ -->
                    <tr>
                        <td align="center" style="background-color: #0f172a; padding: 12px 24px 28px 24px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="border-radius: 12px; background: linear-gradient(135deg, #ef4444, #dc2626, #b91c1c); box-shadow: 0 4px 20px rgba(239, 68, 68, 0.4);">
                                        <a href="{{ url('/') }}" target="_blank" style="font-size: 18px; font-weight: 900; color: #ffffff; text-decoration: none; border-radius: 12px; padding: 18px 0; display: block; text-align: center; letter-spacing: 1px; text-transform: uppercase;">
                                            🔥 EXPLORE ALL DEALS →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ════════════════════════════════════════════════ -->
                    <!-- TRUST STRIP                                     -->
                    <!-- ════════════════════════════════════════════════ -->
                    <tr>
                        <td style="background-color: #1e293b; padding: 24px 16px; border-top: 1px solid #334155; border-bottom: 1px solid #334155;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" width="33%" style="padding: 0 4px;">
                                        <div style="font-size: 28px; margin-bottom: 6px;">🔍</div>
                                        <div style="font-size: 12px; font-weight: 800; color: #f1f5f9;">AI-Verified</div>
                                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Every deal is checked</div>
                                    </td>
                                    <td align="center" width="33%" style="padding: 0 4px; border-left: 1px solid #334155; border-right: 1px solid #334155;">
                                        <div style="font-size: 28px; margin-bottom: 6px;">⚡</div>
                                        <div style="font-size: 12px; font-weight: 800; color: #f1f5f9;">Live Tracking</div>
                                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Prices tracked 24/7</div>
                                    </td>
                                    <td align="center" width="33%" style="padding: 0 4px;">
                                        <div style="font-size: 28px; margin-bottom: 6px;">💰</div>
                                        <div style="font-size: 12px; font-weight: 800; color: #f1f5f9;">Best Prices</div>
                                        <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Guaranteed lowest</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ════════════════════════════════════════════════ -->
                    <!-- FOOTER                                          -->
                    <!-- ════════════════════════════════════════════════ -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 28px 24px 20px 24px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <!-- Brand -->
                                <tr>
                                    <td align="center" style="padding-bottom: 16px;">
                                        <span style="font-size: 18px; font-weight: 900; color: #ffffff;">Latest<span style="color: #ef4444;">Deal</span><span style="color: #475569; font-size: 12px;">.in</span></span>
                                        <p style="margin: 4px 0 0 0; font-size: 11px; color: #475569;">India's AI-Powered Deal Discovery Engine</p>
                                    </td>
                                </tr>
                                <!-- Links -->
                                <tr>
                                    <td align="center" style="padding: 12px 0; border-top: 1px solid #1e293b;">
                                        <a href="{{ url('/') }}" style="color: #94a3b8; text-decoration: none; font-size: 11px; font-weight: 600; padding: 0 10px;">Home</a>
                                        <span style="color: #334155;">•</span>
                                        <a href="{{ url('/categories/electronics') }}" style="color: #94a3b8; text-decoration: none; font-size: 11px; font-weight: 600; padding: 0 10px;">Electronics</a>
                                        <span style="color: #334155;">•</span>
                                        <a href="{{ url('/categories/fashion') }}" style="color: #94a3b8; text-decoration: none; font-size: 11px; font-weight: 600; padding: 0 10px;">Fashion</a>
                                        <span style="color: #334155;">•</span>
                                        <a href="{{ url('/terms') }}" style="color: #94a3b8; text-decoration: none; font-size: 11px; font-weight: 600; padding: 0 10px;">Terms</a>
                                    </td>
                                </tr>
                                <!-- Unsubscribe -->
                                @if(isset($unsubscribeUrl))
                                <tr>
                                    <td align="center" style="padding-top: 12px;">
                                        <p style="margin: 0; font-size: 11px; color: #475569;">
                                            Don't want deal alerts? <a href="{{ $unsubscribeUrl }}" style="color: #ef4444; text-decoration: underline; font-weight: 600;">Unsubscribe</a>
                                        </p>
                                    </td>
                                </tr>
                                @endif
                                <!-- Legal -->
                                <tr>
                                    <td align="center" style="padding-top: 12px;">
                                        <p style="margin: 0; font-size: 10px; color: #334155;">&copy; {{ date('Y') }} LatestDeal.in — All rights reserved.</p>
                                        <p style="margin: 4px 0 0 0; font-size: 10px; color: #334155;">You're receiving this because you subscribed to deal alerts.</p>
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
