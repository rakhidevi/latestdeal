<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\PriceHistory;
use App\Models\Tag;
use App\Events\DealIngested;
use App\Jobs\PublishDealToTelegramJob;
use App\Jobs\PingGoogleIndexingApiJob;
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
            'category_id' => 'nullable|integer',
            'category_name' => 'nullable|string',
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

        // 1.1 Resolve Category from Name or Apply Keyword Rules
        if (empty($validated['category_id'])) {
            $catName = !empty($validated['category_name']) ? $validated['category_name'] : null;
            
            // Keyword Rule Engine (if no category name provided, or if we want to fallback from AI)
            if (empty($catName) && !empty($validated['title'])) {
                $titleLower = strtolower($validated['title']);
                if (preg_match('/\b(cookie|biscuit|chocolate|chips|snack|grocery)\b/', $titleLower)) {
                    $catName = 'Food & Grocery';
                } elseif (preg_match('/\b(phone|mobile|iphone|smartphone|poco|samsung)\b/', $titleLower)) {
                    $catName = 'Mobiles';
                } elseif (preg_match('/\b(tv|laptop|earbuds|headphones|electronics)\b/', $titleLower)) {
                    $catName = 'Electronics';
                } elseif (preg_match('/\b(shoe|shirt|t-shirt|jeans|fashion|wear)\b/', $titleLower)) {
                    $catName = 'Fashion';
                } elseif (preg_match('/\b(face wash|cream|beauty|makeup|perfume)\b/', $titleLower)) {
                    $catName = 'Beauty & Personal Care';
                } elseif (preg_match('/\b(course|udemy|certification)\b/', $titleLower)) {
                    $catName = 'Education';
                }
            }
            
            // Ultimate fallback so we never drop a deal
            if (empty($catName)) {
                $catName = 'Uncategorized';
            }
            
            $cat = \App\Models\Category::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($catName)],
                ['name' => $catName]
            );
            $validated['category_id'] = $cat->id;
        }

        // 1.2 Resolve Merchant from URL Domain
        $host = parse_url($validated['url'], PHP_URL_HOST);
        $resolvedMerchantId = null;
        
        if ($host) {
            $host = preg_replace('/^www\./', '', $host);
            if (in_array($host, ['amzn.to', 'amazon.in', 'amazon.com', 'link.amazon'])) {
                $merchant = \App\Models\Merchant::where('name', 'LIKE', '%Amazon%')->first();
            } else {
                $merchant = \App\Models\Merchant::where('domain', 'LIKE', '%' . $host . '%')->first();
            }
            if ($merchant) {
                $resolvedMerchantId = $merchant->id;
            }
        }

        if (!$resolvedMerchantId) {
            return response()->json([
                'error' => 'Deal rejected: Unsupported merchant domain (' . ($host ?? 'unknown') . ').'
            ], 422);
        }
        $validated['merchant_id'] = $resolvedMerchantId;

        // 1.5 Block Illegal / Pirated Content (Safety Net)
        $blockedKeywords = ['mod apk', 'cracked apk', 'premium unlocked', 'unlocked all', 'crack', 'keygen', 'pirated', 'warez'];
        $titleLower = strtolower($validated['title']);
        foreach ($blockedKeywords as $keyword) {
            if (str_contains($titleLower, $keyword)) {
                return response()->json(['error' => 'Deal rejected: illegal content'], 422);
            }
        }

        \Illuminate\Support\Facades\Log::info('Validated category_id before Deal::create: ' . json_encode($validated['category_id']));

        // 2. Persist Raw Payload (Status: raw)
        $deal = Deal::create([
            'url' => $validated['url'],
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
            'status' => 'raw', // Indicates it hasn't been processed
            'short_url' => $validated['short_url'] ?? null,
        ]);

        // 3. Queue Processing (Dispatch Event)
        $correlationId = Str::uuid()->toString();
        event(new \App\Events\DealDiscovered($deal, $correlationId, 'unknown', '1.0', ['raw_payload' => $validated]));

        // 4. Return HTTP 200 immediately
        return response()->json([
            'message' => 'Deal ingested and queued successfully',
            'deal_id' => $deal->id,
            'correlation_id' => $correlationId
        ], 200);
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
