<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PricePredictionController extends Controller
{
    public function predict(Request $request)
    {
        $request->validate([
            'deal_id' => 'required|exists:deals,id'
        ]);

        $deal = Deal::find($request->deal_id);

        $ollamaBaseUrl = Setting::where('key', 'ollama_base_url')->value('value') ?? env('OLLAMA_BASE_URL', 'https://ai.latestdeal.in');
        $ollamaUrl = rtrim($ollamaBaseUrl, '/') . '/api/generate';
        $model = Setting::where('key', 'ollama_model')->value('value') ?? env('OLLAMA_MODEL', 'llama3');

        $discountPct = 0;
        if ($deal->original_price > 0 && $deal->original_price > $deal->discounted_price) {
            $discountPct = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
        }

        $prompt = "You are an expert AI retail pricing analyst.\n" .
                  "Deal Title: {$deal->title}\n" .
                  "Brand: {$deal->brand}\n" .
                  "Current Price: ₹{$deal->discounted_price}\n" .
                  "Original Price: ₹{$deal->original_price}\n" .
                  "Discount: {$discountPct}%\n\n" .
                  "Task: Predict if this is a good time to buy or if the user should wait for a better price drop.\n" .
                  "Reply ONLY with a raw JSON object containing exactly these keys:\n" .
                  "{\n" .
                  "  \"prediction\": \"A short 1-2 sentence advice.\",\n" .
                  "  \"confidence_score\": 85,\n" .
                  "  \"buy_now\": true\n" .
                  "}\n" .
                  "Do NOT include markdown formatting like ```json, just the raw JSON brackets.";

        try {
            $response = Http::timeout(20)->post($ollamaUrl, [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json'
            ]);

            if ($response->successful()) {
                $jsonString = $response->json('response');
                $result = json_decode($jsonString, true);

                if ($result && isset($result['prediction'])) {
                    return response()->json([
                        'status' => 'success',
                        'data' => $result
                    ]);
                }
            }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to parse AI response'
            ], 500);

        } catch (\Exception $e) {
            Log::error("Price Prediction failed: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'AI connection failed'
            ], 500);
        }
    }
}
