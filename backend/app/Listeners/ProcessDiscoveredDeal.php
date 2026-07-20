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
use Illuminate\Support\Str;

class ProcessDiscoveredDeal implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'default';

    public function handle(DealDiscovered $event)
    {
        $deal = $event->deal;
        $payload = $event->metadata['raw_payload'];

        // 1. Process Base64 Image
        $imagePath = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $payload['image_base64'], $type)) {
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
            PublishDealToTelegramJob::dispatch($deal)->delay(now()->addSeconds(30));
            PingGoogleIndexingApiJob::dispatch($deal)->delay(now()->addMinutes(1));
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
