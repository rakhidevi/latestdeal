<?php

namespace App\Listeners;

use App\Events\DealIngested;
use App\Models\PublisherRule;
use App\Models\SocialAccount;
use App\Jobs\PublishDealToTelegramJob;
use App\Jobs\PublishDealToInstagramJob;
use App\Jobs\PublishDealToFacebookJob;
use App\Jobs\PublishDealToTwitterJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CheckPublisherRules implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(DealIngested $event): void
    {
        $deal = $event->deal;
        $discount = 0;
        if ($deal->original_price > 0) {
            $discount = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
        }

        // Check if this deal matches any publisher rule
        $rules = PublisherRule::where('min_discount', '<=', $discount)->get();

        $shouldPublish = false;

        foreach ($rules as $rule) {
            $keywordMatch = true;
            if ($rule->keyword) {
                if (stripos($deal->title, $rule->keyword) === false) {
                    $keywordMatch = false;
                }
            }
            
            $categoryMatch = true;
            if ($rule->category_id) {
                if ($deal->category_id != $rule->category_id) {
                    $categoryMatch = false;
                }
            }

            if ($keywordMatch && $categoryMatch) {
                $shouldPublish = true;
                break;
            }
        }

        // If rules exist and it matches, publish. Or if there are no rules, maybe we publish by default? 
        // For strict validation, we require a match if any rules exist.
        if (PublisherRule::count() === 0) {
            // Default to publish if no rules are configured
            $shouldPublish = true;
        }

        if ($shouldPublish) {
            Log::info("Deal {$deal->id} matches publisher rules. Dispatching to configured social accounts.");
            
            $accounts = SocialAccount::where('is_active', true)->get();
            foreach ($accounts as $account) {
                if ($account->platform === 'telegram') {
                    PublishDealToTelegramJob::dispatch($deal, $account->id);
                } elseif ($account->platform === 'instagram') {
                    PublishDealToInstagramJob::dispatch($deal, $account->id);
                } elseif ($account->platform === 'facebook') {
                    PublishDealToFacebookJob::dispatch($deal, $account->id);
                } elseif ($account->platform === 'twitter') {
                    PublishDealToTwitterJob::dispatch($deal, $account->id);
                }
            }
        } else {
            Log::info("Deal {$deal->id} did not match any publisher rules. Skipped publishing.");
        }
    }
}
