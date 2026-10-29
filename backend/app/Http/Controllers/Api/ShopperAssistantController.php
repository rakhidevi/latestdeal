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
                // Load exact matched deals directly from the database and strictly preserve ranked order
                $dealsById = Deal::whereIn('id', $dealIds)
                    ->where('status', 'active')
                    ->with(['merchant', 'category'])
                    ->get()
                    ->keyBy('id');

                $deals = collect($dealIds)
                    ->map(function ($id) use ($dealsById) {
                        $deal = $dealsById->get($id);
                        if (!$deal) return null;

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
                    })
                    ->filter()
                    ->values();
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

        // Compute verified mathematical attributes for honest recommendation labeling
        $annotatedDeals = collect([]);
        if (!$noDealsFound) {
            $minPrice = (float) $deals->min(fn($d) => (float)($d['price'] ?? PHP_INT_MAX));
            $maxSavings = (float) $deals->max(fn($d) => (float)($d['savings'] ?? 0));

            $annotatedDeals = $deals->map(function ($deal, $index) use ($minPrice, $maxSavings) {
                $price = (float)($deal['price'] ?? 0);
                $savings = (float)($deal['savings'] ?? 0);
                $isLowest = ($price <= $minPrice && $price > 0);
                $isHighestSavings = ($savings >= $maxSavings && $savings > 0);

                if ($index === 0) {
                    if ($isLowest && $isHighestSavings) {
                        $badge = '🌟 **Top Recommendation (AI Best Match — Lowest Price & Highest Savings)**';
                    } elseif ($isLowest) {
                        $badge = '🌟 **Top Recommendation (AI Best Match & Lowest Price)**';
                    } elseif ($isHighestSavings) {
                        $badge = '🌟 **Top Recommendation (AI Best Match & Highest Savings)**';
                    } else {
                        $badge = '🌟 **Top Recommendation (AI Best Match)**';
                    }
                } else {
                    if ($isLowest) {
                        $badge = '🏷️ **Budget Pick (Lowest Entry Price)**';
                    } elseif ($isHighestSavings) {
                        $badge = '💎 **Best Value Pick (Highest Savings)**';
                    } elseif (($deal['discount_pct'] ?? 0) >= 25) {
                        $badge = '💎 **Value Runner-Up (Strong Discount)**';
                    } else {
                        $badge = '⚡ **Alternative Option**';
                    }
                }

                $deal['is_lowest_price'] = $isLowest;
                $deal['is_highest_savings'] = $isHighestSavings;
                $deal['ai_computed_badge'] = $badge;
                return $deal;
            });
        }

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
            $dealsJson = json_encode($annotatedDeals->values()->toArray());
            $systemPrompt =
                "You are an expert, objective AI Shopping Advisor for LatestDeal.in (India's deal intelligence platform).\n" .
                "The following verified deals were found for the user's shopping request in prioritized algorithmic ranking order:\n\n" .
                $dealsJson . "\n\n" .
                "STRICT SHOPPING ADVISOR RULES:\n" .
                "1. Provide a concise 1-2 sentence high-level shopping insight or buying advice for the shopper (e.g. highlighting key trade-offs or why the top pick stands out).\n" .
                "2. DO NOT output bulleted lists or itemized deal recommendations—the verified recommendations with mathematically computed badges and exact prices are rendered deterministically right below your summary.\n" .
                "3. If the user specified a maximum budget or price ceiling, acknowledge how these options fit within that budget.\n" .
                "4. Keep your tone objective, concise, and helpful.";
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

            if ($noDealsFound) {
                return response()->json([
                    'reply'               => $response['content'],
                    'source'              => $response['provider'],
                    'recommended_deal_ids'=> [],
                ]);
            }

            // Extract high-level opening insight from AI response, avoiding duplicate/hallucinated bullets
            $aiContent = trim($response['content'] ?? '');
            $paragraphs = preg_split('/\n{2,}/', $aiContent);
            $firstPara = trim($paragraphs[0] ?? '');

            // Ensure firstPara does not contain bulleted deal lists or hallucinations
            if (empty($firstPara) || str_starts_with($firstPara, '-') || str_starts_with($firstPara, '*') || str_starts_with($firstPara, '1.')) {
                $intro = "Here are the top catalog recommendations matching your request:";
            } else {
                $intro = $firstPara;
            }

            $deterministicRecommendations = $this->renderDeterministicRecommendations($annotatedDeals);
            $finalReply = $intro . "\n\n" . $deterministicRecommendations;

            return response()->json([
                'reply'               => $finalReply,
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
                $deterministicRecommendations = $this->renderDeterministicRecommendations($annotatedDeals->isNotEmpty() ? $annotatedDeals : $deals);
                $fallbackReply = "Here are the top catalog recommendations matching your request:\n\n" . $deterministicRecommendations;
            }

            return response()->json([
                'reply'               => $fallbackReply,
                'source'              => 'smart_assistant_grounded_engine',
                'recommended_deal_ids'=> $deals->pluck('id')->values(),
            ], 200);
        }
    }

    /**
     * Render recommendations deterministically from verified catalog calculations
     */
    private function renderDeterministicRecommendations(\Illuminate\Support\Collection $annotatedDeals): string
    {
        $topDeals = $annotatedDeals->take(3);
        $lines = [];

        foreach ($topDeals as $d) {
            $badge = $d['ai_computed_badge'] ?? '• **Recommended Offer**';
            $origPrice = (float)($d['original_price'] ?? 0);
            $price = (float)($d['price'] ?? 0);
            $discountPct = (int)($d['discount_pct'] ?? 0);
            $savings = (float)($d['savings'] ?? max(0, $origPrice - $price));

            $origStr = $origPrice > $price ? " (MRP ~~₹" . number_format($origPrice) . "~~)" : "";
            $discountBadge = $discountPct > 0 ? " — **{$discountPct}% OFF**" : "";
            $savingsStr = $savings > 0 ? " — saves ₹" . number_format($savings) : "";

            $targetLink = !empty($d['slug']) ? route('deals.show', $d['slug']) : ($d['deal_url'] ?? $d['url'] ?? '#');

            $extraNote = "";
            if (str_contains(strtolower($d['title'] ?? ''), 'm15')) {
                $extraNote = " *(Limited inventory — delivery depends on pincode)*";
            }

            $lines[] = "{$badge}: **[{$d['title']}]({$targetLink})**\n  ₹" . number_format($price) . "{$origStr}{$discountBadge}{$savingsStr} on *{$d['merchant']}*{$extraNote}";
        }

        $lines[] = "👉 *Prices and stock reflect our latest catalog synchronization. Confirm final price and delivery eligibility on merchant site.*";

        return implode("\n\n", $lines);
    }
}
