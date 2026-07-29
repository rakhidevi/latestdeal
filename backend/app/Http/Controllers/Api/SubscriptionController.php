<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\Device;
use App\Models\PushSubscription;
use App\Models\PriceAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController
{
    /**
     * Subscribe a user/device to native Web Push and alerts.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
            'push_subscription' => 'nullable|array',
            'push_subscription.endpoint' => 'required_with:push_subscription|string',
            'push_subscription.keys.p256dh' => 'required_with:push_subscription|string',
            'push_subscription.keys.auth' => 'required_with:push_subscription|string',
            'device' => 'nullable|array',
            'device.device_key' => 'nullable|string',
            'device.browser' => 'nullable|string',
            'device.platform' => 'nullable|string',
            'preferences' => 'nullable|array',
        ]);

        if (empty($validated['email']) && empty($validated['push_subscription'])) {
            return response()->json(['error' => 'Must provide email or push subscription'], 422);
        }

        // 1. Get or create subscriber
        $email = $validated['email'] ?? null;
        if ($email) {
            $subscriber = Subscriber::firstOrCreate(
                ['email' => $email],
                ['status' => 'active', 'preferences' => $validated['preferences'] ?? null]
            );
        } else {
            $subscriber = Subscriber::create([
                'status' => 'active',
                'preferences' => $validated['preferences'] ?? null,
            ]);
        }

        // 2. Track Multi-Device Fingerprint
        $deviceData = $validated['device'] ?? [];
        $deviceKey = $deviceData['device_key'] ?? md5($request->userAgent() . $request->ip());

        $device = Device::updateOrCreate(
            ['subscriber_id' => $subscriber->id, 'device_key' => $deviceKey],
            [
                'browser' => $deviceData['browser'] ?? $this->getBrowserName($request->userAgent()),
                'platform' => $deviceData['platform'] ?? 'Web',
                'last_seen_at' => now(),
            ]
        );

        // 3. Save / Update W3C Push Subscription
        if (!empty($validated['push_subscription'])) {
            $subData = $validated['push_subscription'];
            PushSubscription::updateOrCreate(
                ['endpoint' => $subData['endpoint']],
                [
                    'subscriber_id' => $subscriber->id,
                    'device_id' => $device->id,
                    'p256dh' => $subData['keys']['p256dh'],
                    'auth' => $subData['keys']['auth'],
                    'last_success_at' => now(),
                    'failure_count' => 0,
                ]
            );
        }

        return response()->json([
            'message' => 'Subscribed successfully to self-hosted notification engine',
            'subscriber_id' => $subscriber->id,
            'device_id' => $device->id,
        ], 201);
    }

    /**
     * Set a target price alert for a subscriber.
     */
    public function setAlert(Request $request)
    {
        $validated = $request->validate([
            'subscriber_id' => 'required|exists:subscribers,id',
            'keyword' => 'required|string',
            'target_price' => 'required|numeric',
        ]);

        $alert = PriceAlert::create($validated);

        return response()->json([
            'message' => 'Price alert created successfully',
            'alert_id' => $alert->id
        ], 201);
    }

    private function getBrowserName(?string $ua): string
    {
        if (!$ua) return 'Unknown';
        if (str_contains($ua, 'Edg')) return 'Edge';
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari')) return 'Safari';
        return 'Browser';
    }
}
