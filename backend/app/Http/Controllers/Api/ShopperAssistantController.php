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
        
        // Fetch cached deals (same cache key as web.php)
        $deals = Cache::remember('deals.assistant', 300, function () {
            return Deal::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->limit(120)
                ->get()
                ->map(function ($deal) {
                    return [
                        'id' => $deal->id,
                        'title' => $deal->title,
                        'price' => (float) $deal->discounted_price,
                        'original_price' => (float) $deal->original_price,
                        'discount_pct' => $deal->original_price > 0 ? round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) : 0,
                        'url' => $deal->url,
                        'merchant' => $deal->merchant->name ?? 'Marketplace',
                    ];
                });
        });

        if (!empty($dealIds)) {
            $deals = collect($deals)->whereIn('id', $dealIds)->values();
        }

        $systemPrompt = "You are an AI Shopping Assistant. Here are the strictly filtered deals available matching the user's budget and criteria: \n\n" . 
                        json_encode($deals) . "\n\n" . 
                        "The user will ask for a recommendation. You MUST ONLY recommend deals from the JSON list above. " . 
                        "Do not invent or hallucinate deals, prices, or models. Pay strict attention to the user's budget. " .
                        "Keep your response concise, friendly, and format it nicely in markdown. Mention the prices and merchants.";

        $ollamaUrl = env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434') . '/api/generate';
        $model = env('OLLAMA_MODEL', 'llama3');

        try {
            $response = Http::timeout(30)->post($ollamaUrl, [
                'model' => $model,
                'prompt' => $systemPrompt . "\n\nUser: " . $userMessage . "\n\nAI Assistant:",
                'stream' => false
            ]);

            if ($response->successful()) {
                $reply = $response->json('response');
                return response()->json(['reply' => $reply]);
            }
            
            return response()->json(['reply' => "Sorry, I am having trouble connecting to my AI core right now."], 500);

        } catch (\Exception $e) {
            return response()->json(['reply' => "I am currently offline or restarting. Please try again in a moment. (" . $e->getMessage() . ")"], 500);
        }
    }
}
