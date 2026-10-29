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

        $savings = max(0, (float)$deal->original_price - (float)$deal->discounted_price);
        $prompt = "You are an expert AI retail pricing analyst.\n" .
                  "Deal Title: {$deal->title}\n" .
                  "Brand: {$deal->brand}\n" .
                  "Current Price: ₹{$deal->discounted_price}\n" .
                  "Original Price (MRP): ₹{$deal->original_price}\n" .
                  "Savings Amount: ₹{$savings}\n" .
                  "Discount: {$discountPct}%\n\n" .
                  "Historical Prices:\n{$historyText}\n\n" .
                  "Task: Predict if this is a good time to buy or if the user should wait for a better price drop based on the historical prices provided.\n" .
                  "CRITICAL: Do NOT confuse the current price (₹{$deal->discounted_price}) with the savings amount (₹{$savings}). If referencing savings, state 'saving ₹" . number_format($savings) . "' or '{$discountPct}% off'.\n" .
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
            $dealQualityScore = (int)($deal->ai_score ?: ($result['confidence_score'] ?? 85));
            $result['confidence_score'] = $dealQualityScore;
            $result['deal_quality_score'] = $dealQualityScore;
            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        }

        // Algorithmic Price Intelligence fallback (Rule 44 in AGENTS.md)
        $brandText = !empty($deal->brand) ? " for {$deal->brand}" : "";
        $formattedPrice = "₹" . number_format($deal->discounted_price);
        $savings = max(0, (float)$deal->original_price - (float)$deal->discounted_price);
        $formattedSavings = "₹" . number_format($savings);

        // Defensive check: If discount is suspiciously high (>75% or MRP > 4x price), flag potential inflated MRP
        $isSuspiciousMrp = ($discountPct > 75) || ($deal->original_price > 0 && ($deal->original_price / max(1, $deal->discounted_price)) > 4.0);

        if ($isSuspiciousMrp) {
            $fallbackPrediction = "High savings claim ({$discountPct}% off listed MRP of ₹" . number_format($deal->original_price) . "). Listed MRP may be inflated by seller. Recommended: verify current market pricing before purchase.";
            $confidence = 62;
            $buyNow = false;
        } elseif ($discountPct >= 50) {
            $fallbackPrediction = "Deep discount: {$discountPct}% off listed MRP (deal price {$formattedPrice}, saving {$formattedSavings}){$brandText}. Pricing is currently well below manufacturer listed baseline.";
            $confidence = 84;
            $buyNow = true;
        } elseif ($discountPct >= 30) {
            $fallbackPrediction = "Solid value: {$discountPct}% off listed MRP (deal price {$formattedPrice}, saving {$formattedSavings}){$brandText}. Significantly below standard catalog pricing.";
            $confidence = 82;
            $buyNow = true;
        } elseif ($discountPct >= 15) {
            $fallbackPrediction = "Fair offer at {$discountPct}% off listed MRP (deal price {$formattedPrice}, saving {$formattedSavings}). Price is stable and competitive; good time to buy if you need this item today.";
            $confidence = 78;
            $buyNow = true;
        } else {
            $fallbackPrediction = "Modest discount of {$discountPct}% (deal price {$formattedPrice}, saving {$formattedSavings}). Historical price drops suggest waiting for an upcoming promotional sale or coupon drop.";
            $confidence = 72;
            $buyNow = false;
        }

        // Deal Quality score uses the consistent deal quality rating (Rule 12 Single Source of Truth)
        $dealQualityScore = (int)($deal->ai_score ?: $confidence);

        return response()->json([
            'status' => 'success',
            'data' => [
                'prediction' => $fallbackPrediction,
                'confidence_score' => $dealQualityScore,
                'deal_quality_score' => $dealQualityScore,
                'buy_now' => $buyNow
            ],
            'source' => 'algorithmic_prediction'
        ], 200);
    }
}
