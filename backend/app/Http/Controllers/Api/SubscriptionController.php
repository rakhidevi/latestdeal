<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\PriceAlert;
use Illuminate\Http\Request;

class SubscriptionController
{
    /**
     * Subscribe a user to general alerts.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
            'push_token' => 'nullable|string',
        ]);

        if (empty($validated['email']) && empty($validated['push_token'])) {
            return response()->json(['error' => 'Must provide email or push token'], 422);
        }

        $subscriber = Subscriber::firstOrCreate(
            ['email' => $validated['email'] ?? ''],
            ['push_token' => $validated['push_token'] ?? null]
        );

        return response()->json([
            'message' => 'Subscribed successfully',
            'subscriber_id' => $subscriber->id
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
}
