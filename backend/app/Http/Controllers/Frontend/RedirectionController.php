<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\UIC\UicAffiliateClick;

class RedirectionController extends Controller
{
    public function go(Request $request, $hashId)
    {
        $deal = Deal::where('hash_id', $hashId)->firstOrFail();

        // If we have UIC payload via cookies/headers or session
        $visitorUuid = $request->cookie('uic_vid') ?? 'unknown';
        $sessionId = $request->cookie('uic_sid') ?? 'unknown';

        // Log the click in UIC
        UicAffiliateClick::create([
            'session_id' => $sessionId,
            'visitor_uuid' => $visitorUuid,
            'deal_id' => $deal->id,
            'merchant_id' => $deal->merchant_id,
            'clicked_url' => $deal->url,
            'ip_hash' => md5($request->ip() . config('app.key')),
            'referrer' => $request->server('HTTP_REFERER')
        ]);

        // Redirect to actual deal URL
        return redirect()->away($deal->url);
    }
}
