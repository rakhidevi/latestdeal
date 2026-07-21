<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\ClickLog;
use App\Models\UIC\UicAffiliateClick;

class RedirectController
{
    /**
     * Handles the cloaked URL redirect and logs the click.
     * Route: GET /go/{deal}
     */
    public function redirect(Request $request, Deal $deal)
    {
        $userAgent = $request->userAgent();
        
        // Basic Bot Sniffing
        // In a true enterprise setup, you would use a package like 'jaybizzle/crawler-detect'
        $botSignatures = [
            'bot', 'crawl', 'slurp', 'spider', 'mediapartners',
            'google', 'facebookexternalhit', 'twitter', 'whatsapp'
        ];
        
        $isBot = false;
        if ($userAgent) {
            foreach ($botSignatures as $signature) {
                if (stripos($userAgent, $signature) !== false) {
                    $isBot = true;
                    break;
                }
            }
        }

        // Log the click in basic ClickLog
        ClickLog::create([
            'deal_id' => $deal->id,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'publisher_integration_id' => $request->query('pub', null),
            'is_bot' => $isBot,
        ]);

        // Log the click in User Intelligence Center (UIC)
        if (!$isBot) {
            $ipHash = md5($request->ip() . config('app.key'));
            $visitorUuid = $request->cookie('uic_vid');
            $sessionId = $request->cookie('uic_sid');

            UicAffiliateClick::create([
                'session_id' => $sessionId,
                'visitor_uuid' => $visitorUuid,
                'deal_id' => $deal->id,
                'merchant_id' => $deal->merchant_id,
                'clicked_url' => $deal->url,
                'ip_hash' => $ipHash,
                'referrer' => $request->server('HTTP_REFERER')
            ]);
        }

        // Issue the HTTP 302 Redirect
        // The getAffiliateUrlAttribute() on the Deal model handles appending the specific store_id
        return redirect()->away($deal->affiliate_url, 302);
    }
}
