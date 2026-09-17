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
        
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $alertQuery = PriceAlert::where('is_fulfilled', false)
            ->where('target_price', '>=', $deal->discounted_price);

        if ($driver === 'sqlite') {
            $alertQuery->whereRaw("LOWER(?) LIKE '%' || LOWER(keyword) || '%'", [$deal->title]);
        } else {
            $alertQuery->whereRaw('LOWER(?) LIKE LOWER(CONCAT("%", keyword, "%"))', [$deal->title]);
        }

        $matchingAlerts = $alertQuery->with('subscriber')->get();

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
