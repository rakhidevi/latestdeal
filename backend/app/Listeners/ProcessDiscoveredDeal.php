<?php

namespace App\Listeners;

use App\Events\DealDiscovered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Deal;
use App\Models\PriceHistory;
use App\Models\Tag;
use App\Models\Setting;
use App\Events\DealIngested;
use App\Jobs\PublishDealToTelegramJob;
use App\Jobs\PingGoogleIndexingApiJob;
use App\Services\DistributionManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessDiscoveredDeal implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public function handle(DealDiscovered $event)
    {
        $deal = $event->deal;
        $payload = $event->metadata['raw_payload'];

        // 1. Process Image
        $imagePath = null;
        if (!empty($payload['image_base64']) && preg_match('/^data:image\/(\w+);base64,/', $payload['image_base64'], $type)) {
            $data = substr($payload['image_base64'], strpos($payload['image_base64'], ',') + 1);
            $type = strtolower($type[1]);

            $data = base64_decode($data);
            if ($data !== false) {
                $fileName = Str::uuid() . '.' . $type;
                $path = public_path('deals');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                file_put_contents($path . '/' . $fileName, $data);
                $imagePath = 'deals/' . $fileName;
            }
        } elseif (!empty($payload['image_url'])) {
            try {
                $response = Http::timeout(10)->get($payload['image_url']);
                if ($response->successful()) {
                    $ext = 'jpg';
                    $contentType = $response->header('Content-Type');
                    if ($contentType && str_contains($contentType, 'image/')) {
                        $ext = explode('/', $contentType)[1];
                    }
                    $fileName = Str::uuid() . '.' . $ext;
                    $path = public_path('deals');
                    if (!file_exists($path)) {
                        mkdir($path, 0755, true);
                    }
                    file_put_contents($path . '/' . $fileName, $response->body());
                    $imagePath = 'deals/' . $fileName;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to download image from URL: " . $e->getMessage());
            }
        }

        // 2. Deduplication (Cross-Source fuzzy matching)
        $titleWords = array_filter(explode(' ', $payload['title']));
        $firstThreeWords = implode(' ', array_slice($titleWords, 0, 3));
        $brand = isset($payload['brand']) ? Str::limit($payload['brand'], 250, '') : null;
        
        $duplicateDeal = null;
        if (strlen($firstThreeWords) > 5) {
            $query = Deal::where('status', '!=', 'expired')
                         ->where('id', '!=', $deal->id)
                         ->where('url', '!=', $payload['url']);
                         
            if ($brand) {
                $query->where('brand', $brand)->where('title', 'LIKE', $firstThreeWords . '%');
            } else {
                $query->where('title', 'LIKE', $firstThreeWords . '%');
            }
            $duplicateDeal = $query->first();
        }

        if ($duplicateDeal) {
            // It's a duplicate. We update the existing deal and delete the raw one.
            if ($payload['discounted_price'] < $duplicateDeal->discounted_price) {
                $duplicateDeal->update([
                    'discounted_price' => $payload['discounted_price'],
                    'original_price' => $payload['original_price'],
                    'url' => $payload['url'],
                    'merchant_id' => $deal->merchant_id,
                ]);
            }
            
            // If the duplicate was stuck in 'raw', activate it!
            if ($duplicateDeal->status === 'raw') {
                $pipelineEnabled = Setting::where('key', 'deal_approval_pipeline')->value('value') === 'enabled';
                $duplicateDeal->update(['status' => $pipelineEnabled ? 'pending' : 'active']);
            }
            
            // If we generated a new image, update the duplicate deal with it
            if ($imagePath) {
                $duplicateDeal->update(['image_path' => $imagePath]);
            }
            
            // Delete the raw deal since it's a duplicate
            $deal->delete();
            $deal = $duplicateDeal;
        } else {
            // Update the raw deal with the processed image path and final status
            $pipelineEnabled = Setting::where('key', 'deal_approval_pipeline')->value('value') === 'enabled';
            $initialStatus = $pipelineEnabled ? 'pending' : 'active';
            
            $updateData = ['status' => $initialStatus];
            if ($imagePath) {
                $updateData['image_path'] = $imagePath;
            }
            
            $deal->update($updateData);

            // Process Tags
            if (!empty($payload['tags'])) {
                $tagIds = [];
                foreach ($payload['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(['slug' => Str::slug($tagName)], ['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
                $deal->tags()->sync($tagIds);
            }

            // Async Jobs (Legacy flow bridging)
            PingGoogleIndexingApiJob::dispatch($deal)->delay(now()->addMinutes(1));
            
            // Distribute to Marketing Channels if it's an active deal
            if ($deal->status === 'active') {
                $distributionManager = new DistributionManager();
                // Pass metadata for effective price calculation
                $distributionManager->distribute($deal, $event->metadata['score_metrics'] ?? []);
            }
        }

        // Log Price History
        PriceHistory::create([
            'deal_id' => $deal->id,
            'price' => $payload['discounted_price'],
            'recorded_at' => now(),
        ]);

        // Fire next event in chain (Legacy)
        event(new DealIngested($deal));
    }
}
