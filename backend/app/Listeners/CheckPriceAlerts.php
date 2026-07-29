<?php

namespace App\Listeners;

use App\Events\DealIngested;
use App\Models\PriceAlert;
use App\Notifications\PriceDropNotification;
use App\Jobs\SendNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class CheckPriceAlerts implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(DealIngested $event): void
    {
        $deal = $event->deal;
        
        // Find all active alerts where keyword matches deal title and target price is satisfied
        $matchingAlerts = PriceAlert::where('is_fulfilled', false)
            ->where('target_price', '>=', $deal->discounted_price)
            ->whereRaw('LOWER(?) LIKE LOWER(CONCAT("%", keyword, "%"))', [$deal->title])
            ->with('subscriber')
            ->get();

        foreach ($matchingAlerts as $alert) {
            $subscriber = $alert->subscriber;
            if ($subscriber && $subscriber->status === 'active') {
                $traceId = 'alert-' . $alert->id . '-' . Str::random(8);

                // Offload to Notification Engine Queue Worker
                SendNotificationJob::dispatch(
                    $subscriber->id,
                    new PriceDropNotification($deal),
                    $traceId
                );
            }
            
            // Mark the alert as fulfilled
            $alert->update(['is_fulfilled' => true]);
        }
    }
}
