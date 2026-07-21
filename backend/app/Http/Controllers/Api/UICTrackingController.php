<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UIC\UicVisitor;
use App\Models\UIC\UicVisitorSession;
use App\Models\UIC\UicPageVisit;
use App\Models\UIC\UicEvent;
use App\Jobs\ResolveGeoLocation;
use Illuminate\Support\Facades\DB;

class UICTrackingController extends Controller
{
    /**
     * Batch ingest events from the frontend tracker (via sendBeacon or standard fetch)
     */
    public function track(Request $request)
    {
        $payload = $request->json()->all() ?: $request->all();
        
        $visitorUuid = $payload['visitor_uuid'] ?? null;
        $sessionId = $payload['session_id'] ?? null;
        
        if (!$visitorUuid || !$sessionId) {
            return response()->json(['error' => 'Missing visitor or session UUID'], 400);
        }

        // Mask/Hash IP for privacy
        $rawIp = $request->ip();
        $ipHash = md5($rawIp . config('app.key')); // simple mask

        // 1. Ensure Visitor Profile Exists
        $visitor = UicVisitor::firstOrCreate(
            ['visitor_uuid' => $visitorUuid],
            ['ip_hash' => $ipHash]
        );
        
        // Update last seen
        $visitor->update(['last_seen' => now()]);

        // 2. Ensure Session Exists
        $session = UicVisitorSession::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'visitor_uuid' => $visitorUuid,
                'device' => $payload['device'] ?? null,
                'browser' => $payload['browser'] ?? null,
                'os' => $payload['os'] ?? null,
                'screen_resolution' => $payload['screen_resolution'] ?? null,
                'language' => $payload['language'] ?? null,
                'timezone' => $payload['timezone'] ?? null,
                'utm_source' => $payload['utm_source'] ?? null,
                'utm_medium' => $payload['utm_medium'] ?? null,
                'utm_campaign' => $payload['utm_campaign'] ?? null,
                'referrer' => $payload['referrer'] ?? null,
                'entry_page' => $payload['url'] ?? null,
            ]
        );

        // Dispatch Geo Resolution if country is null (only happens once per session)
        if (empty($session->country)) {
            ResolveGeoLocation::dispatch($sessionId, $rawIp);
        }

        // 3. Process Events Array
        $events = $payload['events'] ?? [];
        
        foreach ($events as $evt) {
            $type = $evt['type'] ?? 'UNKNOWN';
            
            if ($type === 'PAGE_VIEW') {
                $session->increment('pages_count');
                
                UicPageVisit::create([
                    'session_id' => $sessionId,
                    'visitor_uuid' => $visitorUuid,
                    'url' => $evt['url'] ?? '',
                    'title' => $evt['title'] ?? '',
                    'duration_seconds' => $evt['duration_seconds'] ?? 0,
                    'scroll_depth' => $evt['scroll_depth'] ?? 0,
                ]);
                
            } else {
                $session->increment('events_count');
                
                UicEvent::create([
                    'session_id' => $sessionId,
                    'visitor_uuid' => $visitorUuid,
                    'event_type' => $type,
                    'event_name' => $evt['name'] ?? null,
                    'metadata' => $evt['metadata'] ?? null,
                ]);
            }
        }

        // Update session duration and exit page based on last event
        $session->update([
            'ended_at' => now(),
            'exit_page' => $payload['url'] ?? $session->exit_page,
            'bounce' => ($session->pages_count <= 1 && $session->events_count == 0),
        ]);

        return response()->json(['status' => 'success']);
    }
}
