<?php

namespace App\Listeners;

use App\Events\DealIngested;
use App\Models\PriceAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CheckPriceAlerts implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(DealIngested $event): void
    {
        $deal = $event->deal;
        
        // Find all alerts where the keyword matches the deal title and the target price is met
        $matchingAlerts = PriceAlert::where('is_fulfilled', false)
            ->where('target_price', '>=', $deal->discounted_price)
            ->whereRaw('LOWER(?) LIKE LOWER(CONCAT("%", keyword, "%"))', [$deal->title])
            ->with('subscriber')
            ->get();

        foreach ($matchingAlerts as $alert) {
            $subscriber = $alert->subscriber;
            
            // In a real application, you would dispatch a Mailable or Push Notification job here
            Log::info("Price Alert Triggered! Emailing {$subscriber->email} for deal: {$deal->title}");
            
            // Ping OneSignal REST API if push_token exists
            if ($subscriber->push_token) {
                try {
                    Http::withHeaders([
                        'Authorization' => 'Basic ' . config('services.onesignal.rest_api_key'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post('https://onesignal.com/api/v1/notifications', [
                        'app_id' => config('services.onesignal.app_id'),
                        'include_player_ids' => [$subscriber->push_token],
                        'headings' => ['en' => 'Price Drop Alert!'],
                        'contents' => ['en' => "{$deal->title} has dropped to ₹{$deal->discounted_price}!"],
                        'url' => url('/go/' . $deal->id)
                    ]);
                } catch (\Exception $e) {
                    Log::error("OneSignal Error: " . $e->getMessage());
                }
            }
            
            // Mark the alert as fulfilled so we don't spam them again for this specific keyword target
            $alert->update(['is_fulfilled' => true]);
        }
    }
}
