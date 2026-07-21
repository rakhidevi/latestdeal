<?php

namespace App\Listeners;

class DiscountCalculatorListener
{
    public function handle($event): void
    {
        $deal = $event->deal ?? null;
        if (!$deal) return;

        $original = (float)($deal->original_price ?? 0);
        $discounted = (float)($deal->discounted_price ?? 0);

        if ($original > 0 && $discounted >= 0) {
            $deal->discount_percentage = round((($original - $discounted) * 100.0) / $original, 2);
            $deal->amount_saved = max(0, $original - $discounted);
            $deal->price_drop = $deal->amount_saved;
            $deal->effective_price = $discounted;
        } else {
            $deal->discount_percentage = 0;
            $deal->amount_saved = 0;
            $deal->price_drop = 0;
            $deal->effective_price = $discounted;
        }

        $deal->saveQuietly();
    }
}
