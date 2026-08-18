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

        try {
            $router = app(\App\Services\AI\AIRouter::class);
            $response = $router->chat(
                [['role' => 'user', 'content' => $prompt]],
                ['capabilities' => ['TEXT', 'JSON']]
            );

            $result = json_decode($response['content'], true);
        } catch (\Exception $e) {
            $errors[] = "AI Router Exception: " . $e->getMessage();
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
