<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\ClickLog;

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

        // Log the click
        ClickLog::create([
            'deal_id' => $deal->id,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'publisher_integration_id' => $request->query('pub', null), // e.g. /go/123?pub=4
            'is_bot' => $isBot,
        ]);

        // Issue the HTTP 302 Redirect
        // The getAffiliateUrlAttribute() on the Deal model handles appending the specific store_id
        return redirect()->away($deal->affiliate_url, 302);
    }
}
