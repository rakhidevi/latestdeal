<?php

namespace App\Services\User;

use App\Events\UserInteracted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteractionService
{
    /**
     * Record a user interaction.
     *
     * @param string $interactionType (e.g. 'deal_view', 'deal_save', 'watch_brand', 'price_alert_created')
     * @param string $source (e.g. 'dashboard', 'brand', 'category', 'homepage')
     * @param int|null $dealId
     * @param array $metadata
     */
    public function record(string $interactionType, string $source, ?int $dealId = null, array $metadata = [])
    {
        $request = request();
        
        $userId = Auth::id();
        $sessionId = $request->session()->getId();
        
        // Build request tracking data
        $requestData = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $this->detectDevice($request->userAgent()),
            'platform' => $this->detectPlatform($request->userAgent()),
            'referrer' => $request->headers->get('referer'),
        ];
        
        event(new UserInteracted($userId, $sessionId, $dealId, $interactionType, $source, $metadata, $requestData));
    }
    
    private function detectDevice(?string $userAgent): string
    {
        if (!$userAgent) return 'unknown';
        if (preg_match('/mobile/i', $userAgent)) return 'mobile';
        if (preg_match('/tablet/i', $userAgent)) return 'tablet';
        return 'desktop';
    }
    
    private function detectPlatform(?string $userAgent): string
    {
        if (!$userAgent) return 'unknown';
        if (preg_match('/windows/i', $userAgent)) return 'windows';
        if (preg_match('/macintosh|mac os x/i', $userAgent)) return 'macos';
        if (preg_match('/linux/i', $userAgent)) return 'linux';
        if (preg_match('/android/i', $userAgent)) return 'android';
        if (preg_match('/iphone|ipad|ipod/i', $userAgent)) return 'ios';
        return 'unknown';
    }
}
