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

class PublishDealToInstagramJob implements ShouldQueue
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
        if (!$account || $account->platform !== 'instagram' || !$account->is_active) {
            return;
        }

        $igUserId = $account->target_id;
        $accessToken = $account->access_token;

        if (!$igUserId || !$accessToken) {
            Log::error("Instagram Job Error: Missing IG User ID or access token for account {$account->id}");
            return;
        }

        // Prepare the caption (Instagram doesn't hyperlink URLs in captions)
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

        $caption .= "\n👉 Link in Bio or visit latestdeal.in and search for this deal!\n\n";
        $caption .= "#deals #shopping #latestdeal #sale";

        $imageUrl = "https://latestdeal.in/" . $this->deal->image_path;

        try {
            // Step 1: Create a media container
            $containerResponse = Http::post("https://graph.facebook.com/v19.0/{$igUserId}/media", [
                'image_url' => $imageUrl,
                'caption' => $caption,
                'access_token' => $accessToken,
            ]);

            if (!$containerResponse->successful()) {
                Log::error("Instagram Media Container Error: " . $containerResponse->body());
                if (str_contains($containerResponse->body(), 'Error validating access token') || str_contains($containerResponse->body(), 'Permissions error')) {
                    $account->update(['is_active' => false]);
                }
                return;
            }

            $creationId = $containerResponse->json('id');

            // Step 2: Publish the media container
            $publishResponse = Http::post("https://graph.facebook.com/v19.0/{$igUserId}/media_publish", [
                'creation_id' => $creationId,
                'access_token' => $accessToken,
            ]);

            if (!$publishResponse->successful()) {
                Log::error("Instagram Media Publish Error: " . $publishResponse->body());
            } else {
                Log::info("Successfully published deal {$this->deal->id} to Instagram account {$igUserId}");
            }
        } catch (\Exception $e) {
            Log::error("Instagram Job Exception: " . $e->getMessage());
        }
    }
}
