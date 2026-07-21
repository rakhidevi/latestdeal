<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\ClickLog;
use App\Models\UIC\UicAffiliateClick;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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
        $isBot = (bool) preg_match('/(bot|crawl|slurp|spider|mediapartners|facebookexternalhit)/i', $userAgent);

        // 2. Increment deal analytics counters safely
        if (!$isBot) {
            try {
                if (Schema::hasColumn('deals', 'clicks_count')) {
                    $deal->timestamps = false;
                    $deal->increment('clicks_count');
                    $deal->timestamps = true;
                }
            } catch (\Exception $e) {
                Log::warning('Deal clicks_count increment error: ' . $e->getMessage());
            }
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
            Log::warning('ClickLog create error: ' . $e->getMessage());
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
                Log::warning('UIC Click log error: ' . $e->getMessage());
            }
        }

        // 5. Always issue HTTP 302 Redirect to affiliate URL safely
        $targetUrl = $deal->affiliate_url ?? $deal->url;
        if (empty($targetUrl) || $targetUrl === '#') {
            $targetUrl = '/';
        }

        return redirect()->away($targetUrl, 302);
    }
}
