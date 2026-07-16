<?php

namespace App\Jobs;

use App\Models\Deal;
use App\Models\SocialAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublishDealToFacebookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deal;
    public $accountId;

    /**
     * Create a new job instance.
     */
    public function __construct(Deal $deal, $accountId)
    {
        $this->deal = $deal;
        $this->accountId = $accountId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $account = SocialAccount::find($this->accountId);
        if (!$account || $account->platform !== 'facebook' || !$account->is_active) {
            return;
        }

        $pageId = $account->target_id;
        $accessToken = $account->access_token;

        if (!$pageId || !$accessToken) {
            Log::error("Facebook Job Error: Missing page ID or access token for account {$account->id}");
            return;
        }

        // Prepare the caption
        $discountPct = 0;
        if ($this->deal->original_price > 0 && $this->deal->original_price > $this->deal->discounted_price) {
            $discountPct = round((($this->deal->original_price - $this->deal->discounted_price) / $this->deal->original_price) * 100);
        }

        $caption = "🔥 {$this->deal->title}\n\n";
        $caption .= "💰 Deal Price: ₹{$this->deal->discounted_price}\n";
        if ($this->deal->original_price > $this->deal->discounted_price) {
            $caption .= "📉 Original: ₹{$this->deal->original_price} (Save {$discountPct}%!)\n";
        }
        
        if ($this->deal->coupon_code) {
            $caption .= "\n🏷️ Use Code: {$this->deal->coupon_code}\n";
        }

        $caption .= "\n👉 Grab it here: https://latestdeal.in/deals/{$this->deal->id}\n\n";
        $caption .= "#deals #shopping #latestdeal";

        $imageUrl = "https://latestdeal.in/" . $this->deal->image_path;

        try {
            // Post Photo with caption to Facebook Page
            $response = Http::post("https://graph.facebook.com/v19.0/{$pageId}/photos", [
                'url' => $imageUrl,
                'message' => $caption,
                'access_token' => $accessToken,
            ]);

            if (!$response->successful()) {
                Log::error("Facebook API Error: " . $response->body());
                if (str_contains($response->body(), 'Error validating access token') || str_contains($response->body(), 'Permissions error')) {
                    $account->update(['is_active' => false]);
                    Log::warning("Deactivating Facebook account {$account->id} due to invalid token or permissions.");
                }
            } else {
                Log::info("Successfully published deal {$this->deal->id} to Facebook page {$pageId}");
            }
        } catch (\Exception $e) {
            Log::error("Facebook Job Exception: " . $e->getMessage());
        }
    }
}
