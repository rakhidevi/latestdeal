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
                        'merchant'      => optional($deal->merchant)->name ?? 'Marketplace',
                    ];
                });
        });

        if ($request->has('deal_ids')) {
            $deals = collect($deals)->whereIn('id', $dealIds)->values();
        }

        $systemPrompt =
            "You are an AI Shopping Assistant for LatestDeal.in. " .
            "Here are the active deals available in our database:\n\n" .
            json_encode($deals) . "\n\n" .
            "STRICT RULES:\n" .
            "1. ONLY recommend deals from the JSON list provided above. NEVER invent or hallucinate products.\n" .
            "2. If the user specifies a budget (e.g. 'under 30000'), you MUST NOT recommend any items that cost more than that amount.\n" .
            "3. If the JSON list above is empty `[]`, it means we currently have ZERO products matching their query in our database. You MUST NOT recommend any products. Simply apologize and ask them to try a different search.\n" .
            "4. Format your reply in concise, friendly markdown.\n" .
            "5. Always mention the product name, price, merchant, and discount %.\n" .
            "6. CRITICAL: You MUST format every recommended product name as a clickable Markdown link pointing to its deal page. Use the format: [Product Name](/deal/{id}) (e.g. [ASUS TUF Gaming](/deal/12)).";

        $fullPrompt = $systemPrompt . "\n\nUser request: " . $userMessage . "\n\nAI Assistant (obeying all rules):";

        // ----------------------------------------------------------------
        // Step 1: Try local Ollama if OLLAMA_BASE_URL is set in settings or .env
        // ----------------------------------------------------------------
        
        $dbSettings = \App\Models\Setting::whereIn('key', ['ollama_base_url', 'ollama_model'])->pluck('value', 'key');
        
        $ollamaBaseUrl = $dbSettings['ollama_base_url'] ?? env('OLLAMA_BASE_URL', 'https://ai.latestdeal.in');
        $ollamaError   = null;

        if ($ollamaBaseUrl) {
            $ollamaUrl = rtrim($ollamaBaseUrl, '/') . '/api/generate';
            $model     = $dbSettings['ollama_model'] ?? env('OLLAMA_MODEL', 'llama3');

            try {
                $response = Http::timeout(10)->post($ollamaUrl, [
                    'model'  => $model,
                    'prompt' => $fullPrompt,
                    'stream' => false,
                ]);

                if ($response->successful() && $response->json('response')) {
                    return response()->json([
                        'reply'  => $response->json('response'),
                        'source' => 'ollama',
                    ]);
                }

                $ollamaError = 'Ollama HTTP ' . $response->status();
            } catch (\Exception $e) {
                // Desktop offline or tunnel down — silently fall through to Gemini
                $ollamaError = $e->getMessage();
            }
        }

        // ----------------------------------------------------------------
        // Step 2: Groq fallback (Free tier, extremely fast Llama3)
        // ----------------------------------------------------------------
        $groqKey = env('GROQ_API_KEY');
        $groqError = null;
        
        if ($groqKey) {
            try {
                $groqUrl = 'https://api.groq.com/openai/v1/chat/completions';
                $groqResponse = Http::withToken($groqKey)->timeout(15)->post($groqUrl, [
                    'model' => 'llama3-8b-8192',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful shopping assistant.'],
                        ['role' => 'user', 'content' => $fullPrompt]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1024,
                ]);

                if ($groqResponse->successful()) {
                    $reply = $groqResponse->json('choices.0.message.content');
                    if ($reply) {
                        return response()->json([
                            'reply'  => $reply,
                            'source' => 'groq',
                        ]);
                    }
                }
                $groqError = "Groq HTTP " . $groqResponse->status() . ": " . substr($groqResponse->body(), 0, 100);
            } catch (\Exception $e) {
                $groqError = $e->getMessage();
            }
        }

        // ----------------------------------------------------------------
        // Step 3: Gemini fallback
        // ----------------------------------------------------------------
        $geminiKey = env('GEMINI_API_KEY');

        if (!$geminiKey && !$groqKey && !$ollamaBaseUrl) {
            return response()->json(['reply' => "No AI provider is configured on the server."], 503);
        }

        if ($geminiKey) {
            try {
                $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=' . $geminiKey;

                $geminiResponse = Http::timeout(30)->post($geminiUrl, [
                    'contents' => [[
                        'parts' => [['text' => $fullPrompt]]
                    ]],
                    'generationConfig' => [
                        'temperature'     => 0.7,
                        'maxOutputTokens' => 1024,
                    ],
                ]);

                if ($geminiResponse->successful()) {
                    $reply = $geminiResponse->json('candidates.0.content.parts.0.text');
                    if ($reply) {
                        return response()->json([
                            'reply'  => $reply,
                            'source' => 'gemini',
                        ]);
                    }
                }

                // Gemini returned an error
                $errorBody = $geminiResponse->json('error.message') ?? $geminiResponse->body();
                $technicalError = "Gemini Error: " . substr($errorBody, 0, 300);
            } catch (\Exception $e) {
                $technicalError = "Gemini Exception: " . $e->getMessage();
            }
        } else {
            $technicalError = "Gemini not configured.";
        }
        
        // If we reach here, ALL configured AI providers failed
        if ($ollamaError) $technicalError .= " | Ollama Error: " . $ollamaError;
        if ($groqError) $technicalError .= " | Groq Error: " . $groqError;
        
        \Illuminate\Support\Facades\Log::error("Shopper Assistant Failed: " . $technicalError);
        
        return response()->json([
            'reply' => "I'm currently experiencing high traffic and couldn't process your request right now. Please try again in a few moments!"
        ], 500);
    }
}
