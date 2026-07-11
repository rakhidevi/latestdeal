<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\PriceHistory;
use App\Models\Tag;
use App\Events\DealIngested;
use App\Jobs\PublishDealToTelegramJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Setting;

class DealIngestionController
{
    /**
     * Handles the payload from the Python Local Worker.
     */
    public function store(Request $request)
    {
        // 1. Validate the Request
        // Note: In production, you would also use middleware to check the Bearer token.
        $validated = $request->validate([
            'title' => 'required|string',
            'original_price' => 'required|numeric',
            'discounted_price' => 'required|numeric',
            'url' => 'required|url',
            'category_id' => 'required|integer',
            'merchant_id' => 'nullable|integer', // Made nullable so we can auto-resolve
            'ai_caption' => 'required|string',
            'image_base64' => 'required|string',
            'promo_code' => 'nullable|string',
            'brand' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'verdict' => 'nullable|string',
            'trust_metrics' => 'nullable|string',
            'ai_score' => 'nullable|integer|min:1|max:100',
            'short_url' => 'nullable|url'
        ]);

        // 1.2 Resolve Merchant from URL Domain
        $host = parse_url($validated['url'], PHP_URL_HOST);
        $resolvedMerchantId = null;
        
        if ($host) {
            $host = preg_replace('/^www\./', '', $host);
            
            // Handle common shortlinks / variations
            if (in_array($host, ['amzn.to', 'amazon.in', 'amazon.com'])) {
                $merchant = \App\Models\Merchant::where('name', 'LIKE', '%Amazon%')->first();
            } else {
                $merchant = \App\Models\Merchant::where('domain', 'LIKE', '%' . $host . '%')->first();
            }
            
            if ($merchant) {
                $resolvedMerchantId = $merchant->id;
            }
        }

        // If we couldn't resolve a merchant, reject the deal.
        // This prevents random sites like freecourse.io from being added as Amazon deals.
        if (!$resolvedMerchantId) {
            return response()->json([
                'error' => 'Deal rejected: Unsupported merchant domain (' . ($host ?? 'unknown') . '). Please add this merchant in the Admin Panel first.'
            ], 422);
        }
        
        // Override the validated array with our newly resolved secure merchant ID
        $validated['merchant_id'] = $resolvedMerchantId;

        // 1.5 Block Illegal / Pirated Content (server-side safety net)
        $blockedKeywords = [
            'mod apk', 'modded apk', 'cracked apk',
            'premium unlocked', 'unlocked all', 'pro unlocked',
            'no watermark', 'ad free mod', 'ads removed mod',
            'crack', 'cracked', 'keygen', 'serial key',
            'pirated', 'warez', 'nulled',
            'paid apk free', 'patched apk',
        ];
        $titleLower = strtolower($validated['title']);
        foreach ($blockedKeywords as $keyword) {
            if (str_contains($titleLower, $keyword)) {
                return response()->json([
                    'error' => 'Deal rejected: illegal or pirated content detected',
                    'blocked_keyword' => $keyword,
                ], 422);
            }
        }

        // 2. Process Base64 Image
        $imagePath = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $validated['image_base64'], $type)) {
            $data = substr($validated['image_base64'], strpos($validated['image_base64'], ',') + 1);
            $type = strtolower($type[1]); // jpg, png, etc.

            $data = base64_decode($data);
            
            if ($data !== false) {
                $fileName = Str::uuid() . '.' . $type;
                // Store directly in public folder for easy access without symlinks
                $path = public_path('deals');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                file_put_contents($path . '/' . $fileName, $data);
                $imagePath = 'deals/' . $fileName;
            }
        }

        if (!$imagePath) {
            return response()->json(['error' => 'Invalid image payload'], 400);
        }

        // 2.5 Determine initial status based on Pipeline Setting
        $pipelineEnabled = Setting::where('key', 'deal_approval_pipeline')->value('value') === 'enabled';
        $initialStatus = $pipelineEnabled ? 'pending' : 'active';

        // 3. Create or Update Deal
        $deal = Deal::updateOrCreate(
            ['url' => $validated['url']],
            [
                'category_id' => $validated['category_id'],
                'merchant_id' => $validated['merchant_id'],
                'title' => Str::limit($validated['title'], 250, ''),
                'original_price' => $validated['original_price'],
                'discounted_price' => $validated['discounted_price'],
                'coupon_code' => $validated['promo_code'] ?? null,
                'brand' => isset($validated['brand']) ? Str::limit($validated['brand'], 250, '') : null,
                'features' => $validated['features'] ?? null,
                'verdict' => $validated['verdict'] ?? null,
                'trust_metrics' => isset($validated['trust_metrics']) ? Str::limit($validated['trust_metrics'], 250, '') : null,
                'ai_caption' => $validated['ai_caption'] ?? null,
                'ai_score' => $validated['ai_score'] ?? null,
                'image_path' => $imagePath,
                'status' => $initialStatus,
                'short_url' => $validated['short_url'] ?? null,
            ]
        );

        // Process Tags
        
        // Removed TinyURL generation as per user request
        if (!empty($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tag = Tag::firstOrCreate([
                    'slug' => Str::slug($tagName)
                ], [
                    'name' => $tagName
                ]);
                $tagIds[] = $tag->id;
            }
            $deal->tags()->sync($tagIds);
        }

        // 4. Log Price History
        PriceHistory::create([
            'deal_id' => $deal->id,
            'price' => $validated['discounted_price'],
            'recorded_at' => now(),
        ]);

        // 5. Trigger the Retention Engine Listener
        event(new DealIngested($deal));

        // 6. Trigger the Publishing Engine Queue Job (if configured for auto-publishing)
        if ($deal->status === 'active') {
            $telegramAccounts = \App\Models\SocialAccount::where('platform', 'telegram')
                ->where('is_active', true)
                ->get();
                
            if ($telegramAccounts->isNotEmpty()) {
                foreach ($telegramAccounts as $account) {
                    PublishDealToTelegramJob::dispatch($deal, $account->id);
                }
            } else {
                // Fallback to .env configuration if no database accounts are set up
                PublishDealToTelegramJob::dispatch($deal, null);
            }
        }

        return response()->json([
            'message' => 'Deal ingested successfully',
            'deal_id' => $deal->id
        ], 201);
    }

    /**
     * Marks a deal as expired. Called by the Python Worker.
     */
    public function expire(Deal $deal)
    {
        $deal->update(['status' => 'expired']);
        return response()->json(['message' => 'Deal expired successfully']);
    }
}
