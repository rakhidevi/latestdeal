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
            'asin' => 'required|string',
            'trace_id' => 'required|string',
            'pipeline_run_id' => 'required|string',
            'title' => 'required|string',
            'original_price' => 'required|numeric',
            'discounted_price' => 'required|numeric',
            'calculated_discount' => 'nullable|numeric',
            'url' => 'required|url',
            'category_id' => 'nullable|integer',
            'category_name' => 'nullable|string',
            'merchant_id' => 'nullable|integer', // Made nullable so we can auto-resolve
            'ai_caption' => 'nullable|string',
            'image_base64' => 'nullable|string',
            'image_url' => 'nullable|url',
            'promo_code' => 'nullable|string',
            'brand' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'verdict' => 'nullable|string',
            'trust_metrics' => 'nullable|string',
            'confidence_score' => 'nullable|integer',
            'confidence_reasons' => 'nullable|string',
            'ai_score' => 'nullable|integer|min:1|max:100',
            'short_url' => 'nullable|url',
            'observation_id' => 'required|string',
            'editorial_status' => 'nullable|string', // Will be ignored and forced to AUTO
            'price_intelligence' => 'nullable|array', // Accept factual data
            'secondary_category_ids' => 'nullable|array',
            'secondary_category_ids.*' => 'integer'
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

        // 1.6 Process Image
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

        // Add image_url to payload for the Listener if we don't have a base64
        $validated['image_path'] = $imagePath;
        if (!empty($validated['image_url'])) {
            $validated['image_url'] = $validated['image_url'];
        }

        // 2. Check for Duplicates based on URL (Strict Idempotency)
        $existingUrlDeal = Deal::where('url', $validated['url'])->first();
        if ($existingUrlDeal) {
            $status = 'existing';
            $message = 'Deal already exists. No changes made.';
            
            // If price changed, update price only (do not touch editorial content)
            if ($existingUrlDeal->discounted_price != $validated['discounted_price']) {
                \App\Models\PriceHistory::create([
                    'deal_id' => $existingUrlDeal->id,
                    'price' => $existingUrlDeal->discounted_price,
                    'recorded_at' => now(),
                ]);
                
                $existingUrlDeal->update([
                    'original_price' => $validated['original_price'],
                    'discounted_price' => $validated['discounted_price'],
                    'status' => 'active' // reactivate if it was expired
                ]);
                
                $status = 'updated';
                $message = 'Deal already exists. Price updated.';
            }
            
            return response()->json([
                'status' => $status,
                'message' => $message,
                'deal_id' => $existingUrlDeal->id,
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
        // Since we already checked for existence, this will always create.
        $deal = Deal::create([
            'asin' => $validated['asin'] ?? null,
            'url' => $validated['url'],
            'image_path' => $imagePath,
            'category_id' => $validated['category_id'],
            'merchant_id' => $validated['merchant_id'],
            'brand_id' => $brandId,
            'title' => Str::limit($validated['title'], 250, ''),
            'original_price' => $validated['original_price'],
            'discounted_price' => $validated['discounted_price'],
            'calculated_discount_percent' => $validated['calculated_discount'] ?? null,
            'price_intelligence' => isset($validated['price_intelligence']) ? json_encode($validated['price_intelligence']) : null,
            'coupon_code' => $validated['promo_code'] ?? null,
            'brand' => isset($validated['brand']) ? Str::limit($validated['brand'], 250, '') : null,
            'features' => $validated['features'] ?? null,
            'verdict' => $validated['verdict'] ?? null,
            'trust_metrics' => isset($validated['trust_metrics']) ? $validated['trust_metrics'] : null,
            'confidence_score' => $validated['confidence_score'] ?? null,
            'confidence_reasons' => isset($validated['confidence_reasons']) ? $validated['confidence_reasons'] : null,
            'ai_caption' => $validated['ai_caption'] ?? null,
            'ai_score' => $validated['ai_score'] ?? null,
            'status' => 'active', // Enum supports active/expired
            'editorial_status' => 'DRAFT', // DRAFT status for controlled validation
            'observation_id' => $validated['observation_id'],
            'trace_id' => $validated['trace_id'] ?? null,
            'pipeline_run_id' => $validated['pipeline_run_id'] ?? null,
            'short_url' => $validated['short_url'] ?? null,
        ]);

        // 4. Queue Processing (Dispatch Event)
        $correlationId = Str::uuid()->toString();
        event(new \App\Events\DealDiscovered($deal, $correlationId, 'unknown', '1.0', ['raw_payload' => $validated]));
        
        // 4.5 Save secondary categories
        if (!empty($validated['secondary_category_ids'])) {
            $deal->categories()->syncWithoutDetaching($validated['secondary_category_ids']);
        }

        // 5. Return HTTP 200 immediately
        return response()->json([
            'status' => 'created',
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

    /**
     * API endpoint to remotely trigger AI and QA pipeline for specific deals.
     */
    public function processPipeline(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'deal_ids' => 'required|array',
            'deal_ids.*' => 'integer|exists:deals,id'
        ]);

        $dealIds = $validated['deal_ids'];
        $results = [
            'processed' => 0,
            'in_review' => 0,
            'qa_failed' => 0,
            'failed' => 0
        ];

        if (count($dealIds) === 0) {
            return response()->json($results);
        }

        // Trigger AI Summarization on the specific deals
        try {
            \Illuminate\Support\Facades\Artisan::call('deals:summarize', [
                '--pilot' => true,
                '--deal-ids' => implode(',', $dealIds)
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Pipeline process error: " . $e->getMessage());
        }

        // Tally results
        foreach ($dealIds as $dealId) {
            $deal = \App\Models\Deal::find($dealId);
            if (!$deal) continue;

            $results['processed']++;
            
            if ($deal->editorial_status === \App\Models\Deal::STATUS_IN_REVIEW) {
                $results['in_review']++;
            } elseif ($deal->editorial_status === \App\Models\Deal::STATUS_QUALITY_CHECK || $deal->editorial_status === \App\Models\Deal::STATUS_REJECTED) {
                $results['qa_failed']++;
            } else {
                $results['failed']++;
            }
        }

        return response()->json($results);
    }

    /**
     * API endpoint to get current deal counts in the pipeline.
     */
    public function pipelineStatus()
    {
        $statusCounts = \Illuminate\Support\Facades\DB::table('deals')
            ->select('editorial_status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('editorial_status')
            ->get();
            
        return response()->json($statusCounts);
    }

    /**
     * Production endpoint to securely receive fully assembled AI payloads from the local worker.
     */
    public function productionSync(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'asin' => 'required|string',
            'title' => 'required|string',
            'brand' => 'required|string',
            'category_id' => 'required|integer',
            'original_price' => 'required|numeric',
            'discounted_price' => 'required|numeric',
            'calculated_discount_percent' => 'required|numeric',
            'url' => 'required|url',
            'short_url' => 'nullable|url',
            'image_url' => 'nullable|string',
            'editorial_summary' => 'required|string',
            'editorial_verdict' => 'required|string',
            'pros' => 'required|array',
            'cons' => 'present|array',
            'qa_status' => 'required|string|in:PASSED',
            'trace_id' => 'required|string',
            'pipeline_run_id' => 'required|string'
        ]);

        // Business logic validation
        if ($validated['discounted_price'] >= $validated['original_price']) {
            return response()->json(['error' => 'Discounted price must be less than original price.'], 422);
        }

        // Prevent duplicate ASINs but handle idempotency
        $existingDeal = Deal::where('asin', $validated['asin'])->first();
        if ($existingDeal) {
            if ($existingDeal->trace_id === $validated['trace_id']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Already synchronized by us',
                    'deal_id' => $existingDeal->id
                ], 200);
            }
            return response()->json(['error' => 'Deal with this ASIN already exists but belongs to another record.'], 409);
        }

        $deal = Deal::create([
            'asin' => $validated['asin'],
            'title' => $validated['title'],
            'brand' => $validated['brand'],
            'category_id' => $validated['category_id'],
            'original_price' => $validated['original_price'],
            'discounted_price' => $validated['discounted_price'],
            'calculated_discount_percent' => $validated['calculated_discount_percent'],
            'url' => $validated['url'],
            'short_url' => $validated['short_url'],
            'image_path' => $validated['image_url'] ?? 'deals/default.png',
            'editorial_summary' => $validated['editorial_summary'],
            'editorial_verdict' => $validated['editorial_verdict'],
            'pros' => $validated['pros'],
            'cons' => $validated['cons'],
            'trace_id' => $validated['trace_id'],
            'pipeline_run_id' => $validated['pipeline_run_id'],
            
            // Set editorial status to IN_REVIEW immediately (bypassing DRAFT and QA)
            'editorial_status' => Deal::STATUS_IN_REVIEW,
            'status' => 'active',
            
            // Optional/Empty fields
            'brand_id' => null,
            'merchant_id' => 1, // Assume Amazon for now
        ]);

        return response()->json([
            'status' => 'success',
            'deal_id' => $deal->id
        ], 201);
    }
}
