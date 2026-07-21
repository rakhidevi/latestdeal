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
        
        // 1.5.1 Block Zero Price Deals (Out of Stock items parsed incorrectly)
        if (empty($validated['discounted_price']) || $validated['discounted_price'] <= 0) {
            return response()->json(['error' => 'Deal rejected: Discounted price cannot be 0 (likely out of stock)'], 422);
        }

        \Illuminate\Support\Facades\Log::info('Validated category_id before Deal::create: ' . json_encode($validated['category_id']));

        // 1.6 Process Image Base64
        $imagePath = 'deals/default.png';
        if (!empty($validated['image_base64'])) {
            $base64Str = $validated['image_base64'];
            $type = 'png';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Str, $matches)) {
                $base64Str = substr($base64Str, strpos($base64Str, ',') + 1);
                $type = strtolower($matches[1]);
            }
            $imageName = Str::random(20) . '.' . $type;
            \Illuminate\Support\Facades\Storage::disk('public')->put('deals/' . $imageName, base64_decode($base64Str));
            $imagePath = 'deals/' . $imageName;
        }

        // 2. Check for Duplicates
        $existingDeal = Deal::where(function($query) use ($validated) {
                $query->where('title', Str::limit($validated['title'], 250, ''))
                      ->where('merchant_id', $validated['merchant_id']);
            })
            ->orWhere('url', $validated['url'])
            ->first();

        if ($existingDeal) {
            // It's a duplicate! Update price if changed
            if ($existingDeal->discounted_price != $validated['discounted_price']) {
                // Save to price history
                \App\Models\PriceHistory::create([
                    'deal_id' => $existingDeal->id,
                    'price' => $existingDeal->discounted_price,
                    'recorded_at' => now(),
                ]);
                
                $existingDeal->update([
                    'original_price' => $validated['original_price'],
                    'discounted_price' => $validated['discounted_price'],
                    'status' => 'active' // reactivate if it was expired
                ]);
                
                return response()->json([
                    'message' => 'Deal already exists. Price updated.',
                    'deal_id' => $existingDeal->id,
                    'correlation_id' => null
                ], 200);
            }
            
            // Just reactivate if it was expired
            if ($existingDeal->status === 'expired') {
                $existingDeal->update(['status' => 'active']);
            }

            return response()->json([
                'message' => 'Deal already exists. No changes made.',
                'deal_id' => $existingDeal->id,
                'correlation_id' => null
            ], 200);
        }

        // 2.5 Resolve Brand ID
        $brandId = null;
        if (!empty($validated['brand'])) {
            $brandName = trim(Str::limit($validated['brand'], 250, ''));
            $slug = Str::slug($brandName);
            if (!empty($slug)) {
                $brand = \App\Models\Brand::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $brandName, 'is_active' => true]
                );
                $brandId = $brand->id;
            }
        }

        // 3. Persist Raw Payload (Status: raw)
        $deal = Deal::create([
            'url' => $validated['url'],
            'image_path' => $imagePath,
            'category_id' => $validated['category_id'],
            'merchant_id' => $validated['merchant_id'],
            'brand_id' => $brandId,
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

        // 4. Queue Processing (Dispatch Event)
        $correlationId = Str::uuid()->toString();
        event(new \App\Events\DealDiscovered($deal, $correlationId, 'unknown', '1.0', ['raw_payload' => $validated]));

        // 5. Return HTTP 200 immediately
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
