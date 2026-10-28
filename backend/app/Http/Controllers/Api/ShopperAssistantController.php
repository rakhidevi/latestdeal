<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Deal;

class ShopperAssistantController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message'  => 'required|string|max:1000',
            'deal_ids' => 'nullable|array',
            'history'  => 'nullable|array'
        ]);

        $userMessage = trim($request->message);
        $dealIds = $request->deal_ids ?? [];
        $rawHistory = $request->history ?? [];

        // 1. Resolve Deals
        if ($request->has('deal_ids')) {
            $dealIds = $request->deal_ids ?? [];
            if (!empty($dealIds)) {
                // Load exact matched deals directly from the database
                $deals = Deal::whereIn('id', $dealIds)
                    ->where('status', 'active')
                    ->with(['merchant', 'category'])
                    ->get()
                    ->map(function ($deal) {
                        $origPrice = (float) $deal->original_price;
                        $price = (float) $deal->discounted_price;
                        $discountPct = $origPrice > $price && $origPrice > 0
                            ? round((($origPrice - $price) / $origPrice) * 100)
                            : 0;

                        return [
                            'id'            => $deal->id,
                            'title'         => $deal->title,
                            'slug'          => $deal->slug,
                            'deal_url'      => route('deals.show', $deal->slug),
                            'price'         => $price,
                            'original_price'=> $origPrice,
                            'discount_pct'  => $discountPct,
                            'savings'       => max(0, $origPrice - $price),
                            'url'           => $deal->url,
                            'image_url'     => $deal->image_url,
                            'merchant'      => optional($deal->merchant)->name ?? 'Amazon India',
                            'ai_score'      => $deal->ai_score ?? 88,
                        ];
                    });
            } else {
                // Caller passed deal_ids as an empty array, meaning 0 deals matched criteria
                $deals = collect([]);
            }
        } else {
            // No deal_ids provided: run query through SmartSearchController to extract constraints
            $isGreeting = preg_match('/^(hi|hello|hey|help|greetings|start)(\s.*)?$/i', $userMessage);
            if ($isGreeting) {
                $deals = Cache::remember('deals.assistant.latest', 120, function () {
                    return Deal::where('status', 'active')
                        ->where('discounted_price', '>', 0)
                        ->orderBy('created_at', 'desc')
                        ->limit(6)
                        ->with('merchant')
                        ->get()
                        ->map(function ($deal) {
                            $origPrice = (float) $deal->original_price;
                            $price = (float) $deal->discounted_price;
                            $discountPct = $origPrice > $price && $origPrice > 0
                                ? round((($origPrice - $price) / $origPrice) * 100)
                                : 0;

                            return [
                                'id'            => $deal->id,
                                'title'         => $deal->title,
                                'slug'          => $deal->slug,
                                'deal_url'      => route('deals.show', $deal->slug),
                                'price'         => $price,
                                'original_price'=> $origPrice,
                                'discount_pct'  => $discountPct,
                                'savings'       => max(0, $origPrice - $price),
                                'url'           => $deal->url,
                                'image_url'     => $deal->image_url,
                                'merchant'      => optional($deal->merchant)->name ?? 'Amazon India',
                                'ai_score'      => $deal->ai_score ?? 85,
                            ];
                        });
                });
            } else {
                $smartSearch = app(SmartSearchController::class);
                $searchRequest = new Request(['q' => $userMessage]);
                $searchResponse = $smartSearch->search($searchRequest);
                $searchData = $searchResponse->getData(true);
                $deals = collect($searchData['deals'] ?? []);
            }
        }

        $noDealsFound = $deals->isEmpty();

        // 2. Structured Grounded Prompt Construction
        if ($noDealsFound) {
            $systemPrompt =
                "You are an expert AI Shopping Assistant for LatestDeal.in.\n" .
                "The search query returned 0 active deals matching: \"" . e($userMessage) . "\".\n\n" .
                "STRICT RULES:\n" .
                "1. Honestly inform the user that no verified deals currently meet all their exact criteria.\n" .
                "2. Suggest actionable alternatives (e.g. broadening the budget, checking related product categories, or viewing /deals/all).\n" .
                "3. NEVER invent or hallucinate product deals that do not exist.";
        } else {
            $dealsJson = json_encode($deals->values()->toArray());
            $systemPrompt =
                "You are an expert, objective AI Shopping Advisor for LatestDeal.in (India's deal intelligence platform).\n" .
                "The following verified deals were found for the user's shopping request:\n\n" .
                $dealsJson . "\n\n" .
                "STRICT SHOPPING ADVISOR RULES:\n" .
                "1. ONLY reference and recommend deals present in the JSON list above. NEVER invent products or prices.\n" .
                "2. If the user specified a maximum budget or price ceiling, strictly obey it.\n" .
                "3. Distinguish between core products and accessories. If the user asked for a device (e.g. phone, laptop, headphones), recommend the actual device, NOT an accessory.\n" .
                "4. Provide explainable recommendations:\n" .
                "   - Highlight the 'Best Match' (best fit for user intent and specifications).\n" .
                "   - Highlight the 'Best Value' (highest verified rupee and percentage savings below MRP).\n" .
                "   - Highlight the 'Budget Pick' if a lower-priced genuine option is available.\n" .
                "5. Always mention exact prices in ₹ (INR), verified savings, and merchant name.\n" .
                "6. Format with clean, structured markdown bullets and bold headers.";
        }

        // 3. Assemble Bounded Multi-Turn History
        $chatMessages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        if (is_array($rawHistory) && !empty($rawHistory)) {
            // Take the last 4 valid turns
            $validTurns = array_slice($rawHistory, -4);
            foreach ($validTurns as $turn) {
                if (!empty($turn['role']) && !empty($turn['content']) && in_array($turn['role'], ['user', 'assistant'])) {
                    // Truncate any overly long past messages to 400 chars
                    $chatMessages[] = [
                        'role' => $turn['role'],
                        'content' => mb_substr((string)$turn['content'], 0, 400)
                    ];
                }
            }
        }

        // Add current user request
        $chatMessages[] = [
            'role' => 'user',
            'content' => "User request: " . $userMessage
        ];

        // 4. Log AI Query to UIC Platform
        try {
            $visitorUuid = $request->cookie('uic_vid') ?? $request->header('X-Visitor-UUID') ?? 'unknown';
            $sessionId = $request->cookie('uic_sid') ?? $request->header('X-Session-ID') ?? 'unknown';
            
            $intent = 'General';
            if (preg_match('/vs|compare|difference/i', $userMessage)) $intent = 'Comparison';
            elseif (preg_match('/best|recommend|top|under|budget/i', $userMessage)) $intent = 'Recommendation';
            elseif (preg_match('/coupon|discount|code|deal/i', $userMessage)) $intent = 'Coupon';

            if (class_exists(\App\Models\UIC\UicAiConversation::class)) {
                \App\Models\UIC\UicAiConversation::create([
                    'session_id' => $sessionId,
                    'visitor_uuid' => $visitorUuid,
                    'question' => $userMessage,
                    'intent' => $intent,
                    'brand_detected' => \App\Services\Catalog\BrandResolver::resolveFromText($userMessage),
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore analytics logging exception
        }

        // 5. Query AIRouter or fallback to grounded intelligence
        try {
            $router = app(\App\Services\AI\AIRouter::class);
            $response = $router->chat(
                $chatMessages,
                ['capabilities' => ['TEXT']]
            );

            return response()->json([
                'reply'               => $response['content'],
                'source'              => $response['provider'],
                'recommended_deal_ids'=> $deals->pluck('id')->values(),
            ]);

        } catch (\Throwable $e) {
            Log::warning("Shopper Assistant AI Router fallback engaged: " . $e->getMessage());

            if ($noDealsFound) {
                $fallbackReply = "I couldn't find any active deals matching **\"" . e($userMessage) . "\"** right now.\n\n" .
                    "💡 **Tips to find the right deal:**\n" .
                    "- Try searching for broad categories like **Smartphones**, **Laptops**, or **Headphones**\n" .
                    "- Increase your budget threshold slightly to view near-budget offers\n" .
                    "- Browse our live [All Deals Hub](/deals/all) for today's highest percentage discounts!";
            } else {
                $topDeals = $deals->take(3);
                $fallbackReply = "Here are the verified recommendations matching your request:\n\n";
                
                $ranks = ['🌟 **Top Recommendation**', '💎 **Best Value Pick**', '🏷️ **Budget Option**'];
                foreach ($topDeals as $idx => $d) {
                    $badge = $ranks[$idx] ?? '•';
                    $origStr = $d['original_price'] > $d['price'] ? " (MRP ~~₹" . number_format($d['original_price']) . "~~)" : "";
                    $discountBadge = $d['discount_pct'] > 0 ? " — **{$d['discount_pct']}% OFF**" : "";
                    $savingsStr = $d['savings'] > 0 ? " — saves ₹" . number_format($d['savings']) : "";
                    
                    $targetLink = !empty($d['slug']) ? route('deals.show', $d['slug']) : ($d['deal_url'] ?? $d['url']);
                    $fallbackReply .= "{$badge}: **[{$d['title']}]({$targetLink})**\n";
                    $fallbackReply .= "  ₹" . number_format($d['price']) . "{$origStr}{$discountBadge}{$savingsStr} on *{$d['merchant']}*\n\n";
                }
                $fallbackReply .= "👉 *Select any product in the comparison matrix below to view price history telemetry and real-time multi-store price comparisons!*";
            }

            return response()->json([
                'reply'               => $fallbackReply,
                'source'              => 'smart_assistant_grounded_engine',
                'recommended_deal_ids'=> $deals->pluck('id')->values(),
            ], 200);
        }
    }
}
