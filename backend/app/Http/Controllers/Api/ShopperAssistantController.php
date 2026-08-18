<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Deal;

class ShopperAssistantController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'deal_ids' => 'nullable|array'
        ]);

        $userMessage = $request->message;
        $dealIds = $request->deal_ids ?? [];

        // Fetch cached deals
        $deals = Cache::remember('deals.assistant', 300, function () {
            return Deal::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->limit(120)
                ->get()
                ->map(function ($deal) {
                    return [
                        'id'            => $deal->id,
                        'title'         => $deal->title,
                        'price'         => (float) $deal->discounted_price,
                        'original_price'=> (float) $deal->original_price,
                        'discount_pct'  => $deal->original_price > 0
                            ? round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100)
                            : 0,
                        'url'           => $deal->url,
                        'image_url'     => $deal->image_url,
                        'image_path'    => $deal->image_url,
                        'merchant'      => optional($deal->merchant)->name ?? 'Marketplace',
                    ];
                });
        });

        $noDealsFound = false;
        if ($request->has('deal_ids')) {
            $dealIds = (array) $request->deal_ids;
            if (empty($dealIds)) {
                $noDealsFound = true;
                $deals = collect();
            } else {
                $deals = collect($deals)->whereIn('id', $dealIds)->values();
            }
        }

        if ($noDealsFound) {
            $systemPrompt =
                "You are an AI Shopping Assistant for LatestDeal.in. " .
                "The search returned 0 matching deals in our database for the request: \"" . $userMessage . "\".\n\n" .
                "STRICT RULE:\n" .
                "Politely inform the user that no active deals matching \"" . $userMessage . "\" were found right now. Suggest that they adjust their budget or search for another product category. Do NOT state that deals were found!";
        } else {
            $systemPrompt =
                "You are an AI Shopping Assistant for LatestDeal.in. " .
                "Here are the active deals available in our database:\n\n" .
                json_encode($deals) . "\n\n" .
                "STRICT RULES:\n" .
                "1. ONLY recommend deals from the JSON list provided above.\n" .
                "2. If the user specifies a budget (e.g. 'under 30000'), you MUST NOT recommend any items that cost more than that amount.\n" .
                "3. If NO deals in the JSON list match the user's exact criteria, you MUST politely state that no active deals matching their request were found right now.\n" .
                "4. Format your reply in concise, friendly markdown.\n" .
                "5. Always mention the product name, price, merchant, and discount %.";
        }

        $fullPrompt = $systemPrompt . "\n\nUser request: " . $userMessage . "\n\nAI Assistant (obeying all rules):";

        // Log AI Query to UIC Platform
        try {
            $visitorUuid = $request->cookie('uic_vid') ?? $request->header('X-Visitor-UUID') ?? 'unknown';
            $sessionId = $request->cookie('uic_sid') ?? $request->header('X-Session-ID') ?? 'unknown';
            
            $intent = 'General';
            if (preg_match('/vs|compare|difference/i', $userMessage)) $intent = 'Comparison';
            elseif (preg_match('/best|recommend|top|under|budget/i', $userMessage)) $intent = 'Recommendation';
            elseif (preg_match('/coupon|discount|code|deal/i', $userMessage)) $intent = 'Coupon';

            \App\Models\UIC\UicAiConversation::create([
                'session_id' => $sessionId,
                'visitor_uuid' => $visitorUuid,
                'question' => $userMessage,
                'intent' => $intent,
                'brand_detected' => \App\Services\Catalog\BrandResolver::resolveFromText($userMessage),
            ]);
        } catch (\Exception $e) {
            // Ignore logging error
        }

        // ----------------------------------------------------------------
        // Step 1: Query the AI Router for a response
        // ----------------------------------------------------------------
        try {
            $router = app(\App\Services\AI\AIRouter::class);
            $response = $router->chat(
                [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "User request: " . $userMessage]
                ],
                ['capabilities' => ['TEXT']]
            );

            return response()->json([
                'reply'  => $response['content'],
                'source' => $response['provider'],
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Shopper Assistant Failed: " . $e->getMessage());
            
            return response()->json([
                'reply' => "I'm currently experiencing high traffic and couldn't process your request right now. Please try again in a few moments!"
            ], 500);
        }
        
        return response()->json([
            'reply' => "I'm currently experiencing high traffic and couldn't process your request right now. Please try again in a few moments!"
        ], 500);
    }
}
