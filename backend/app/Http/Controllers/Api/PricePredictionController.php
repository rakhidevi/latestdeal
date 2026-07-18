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

        $priceHistories = $deal->priceHistories()->orderBy('created_at', 'asc')->get();
        $historyText = "No historical data available.";
        if ($priceHistories->count() > 0) {
            $historyLines = [];
            foreach ($priceHistories as $ph) {
                $date = $ph->created_at->format('Y-m-d');
                $historyLines[] = "- {$date}: ₹{$ph->price}";
            }
            $historyText = implode("\n", $historyLines);
        }

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
                  "Historical Prices:\n{$historyText}\n\n" .
                  "Task: Predict if this is a good time to buy or if the user should wait for a better price drop based on the historical prices provided.\n" .
                  "Reply ONLY with a raw JSON object containing exactly these keys:\n" .
                  "{\n" .
                  "  \"prediction\": \"A short 1-2 sentence advice.\",\n" .
                  "  \"confidence_score\": 85,\n" .
                  "  \"buy_now\": true\n" .
                  "}\n" .
                  "Do NOT include markdown formatting like ```json, just the raw JSON brackets.";

        $errors = [];
        $result = null;

        // Try Ollama
        if ($ollamaBaseUrl) {
            try {
                $response = Http::timeout(10)->post($ollamaUrl, [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'format' => 'json'
                ]);

                if ($response->successful()) {
                    $jsonString = $response->json('response');
                    $result = json_decode($jsonString, true);
                } else {
                    $errors[] = "Ollama HTTP " . $response->status();
                }
            } catch (\Exception $e) {
                $errors[] = "Ollama Exception: " . $e->getMessage();
            }
        }

        // Try Groq if Ollama failed
        if (!$result && env('GROQ_API_KEY')) {
            try {
                $groqUrl = 'https://api.groq.com/openai/v1/chat/completions';
                $response = Http::withToken(env('GROQ_API_KEY'))->timeout(15)->post($groqUrl, [
                    'model' => 'llama3-8b-8192',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.3,
                    'response_format' => ['type' => 'json_object']
                ]);

                if ($response->successful()) {
                    $reply = $response->json('choices.0.message.content');
                    $result = json_decode($reply, true);
                } else {
                    $errors[] = "Groq HTTP " . $response->status();
                }
            } catch (\Exception $e) {
                $errors[] = "Groq Exception: " . $e->getMessage();
            }
        }

        // Try Gemini if Groq failed
        if (!$result && env('GEMINI_API_KEY')) {
            try {
                $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite:generateContent?key=' . env('GEMINI_API_KEY');
                $response = Http::timeout(30)->post($geminiUrl, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'responseMimeType' => 'application/json'
                    ],
                ]);

                if ($response->successful()) {
                    $reply = $response->json('candidates.0.content.parts.0.text');
                    $result = json_decode($reply, true);
                } else {
                    $errors[] = "Gemini HTTP " . $response->status();
                }
            } catch (\Exception $e) {
                $errors[] = "Gemini Exception: " . $e->getMessage();
            }
        }

        if ($result && isset($result['prediction'])) {
            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        }

        Log::error("Price Prediction completely failed. Errors: " . implode(" | ", $errors));
        return response()->json([
            'status' => 'error',
            'message' => 'AI connection failed or invalid JSON response'
        ], 500);
    }
}
