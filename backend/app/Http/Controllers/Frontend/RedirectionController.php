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

        // Log the click in UIC safely
        try {
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
                'ip_hash' => md5($request->ip() . config('app.key')),
                'referrer' => $request->server('HTTP_REFERER')
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('RedirectionController UIC log error: ' . $e->getMessage());
        }

        // Redirect to actual deal URL
        return redirect()->away($deal->affiliate_url ?? $deal->url);
    }
}
