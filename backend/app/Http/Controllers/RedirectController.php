<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\ClickLog;
use App\Models\UIC\UicAffiliateClick;

class RedirectController extends Controller
{
    /**
     * Handle the deal affiliate redirection and analytics logging.
     * Route: /go/{deal:hash_id}
     */
    public function redirect(Request $request, Deal $deal)
    {
        // 1. Detect if request is from a bot / crawler
        $userAgent = $request->header('User-Agent', '');
        $isBot = preg_match('/(bot|crawl|slurp|spider|mediapartners|facebookexternalhit)/i', $userAgent);

        // 2. Increment deal analytics counters
        if (!$isBot) {
            $deal->timestamps = false;
            $deal->increment('clicks_count');
            $deal->timestamps = true;
        }

        // 3. Log the click in basic ClickLog
        try {
            ClickLog::create([
                'deal_id' => $deal->id,
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'publisher_integration_id' => $request->query('pub', null),
                'is_bot' => $isBot,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('ClickLog create error: ' . $e->getMessage());
        }

        // 4. Log the click in User Intelligence Center (UIC)
        if (!$isBot) {
            try {
                $ipHash = md5($request->ip() . config('app.key'));
                $visitorUuid = $request->cookie('uic_vid') ?? 'unknown';
                $sessionId = $request->cookie('uic_sid') ?? 'unknown';

                UicAffiliateClick::create([
                    'session_id' => $sessionId,
                    'visitor_uuid' => $visitorUuid,
                    'merchant' => $deal->merchant->name ?? 'Store',
                    'merchant_id' => $deal->merchant_id,
                    'product' => $deal->title,
                    'deal_id' => $deal->id,
                    'category' => $deal->category->name ?? 'General',
                    'affiliate_url' => $deal->affiliate_url ?? $deal->url,
                    'clicked_url' => $deal->url,
                    'destination' => $deal->url,
                    'ip_hash' => $ipHash,
                    'referrer' => $request->server('HTTP_REFERER')
                ]);
            } catch (\Exception $e) {
                // Ensure tracking failure NEVER breaks the affiliate redirect!
                \Illuminate\Support\Facades\Log::warning('UIC Click log error: ' . $e->getMessage());
            }
        }

        // 5. Issue the HTTP 302 Redirect to affiliate URL
        return redirect()->away($deal->affiliate_url ?? $deal->url, 302);
    }
}
