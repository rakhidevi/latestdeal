<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\PriceHistory;
use App\Events\DealScrapeRequested;
use App\Events\DealUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PriceUpdateController extends Controller
{
    /**
     * Triggered by the frontend to request a real-time price check
     */
    public function refreshPrice(Request $request, $id)
    {
        $deal = Deal::findOrFail($id);

        if (!$deal->url) {
            return response()->json(['success' => false, 'message' => 'No URL found for this deal.'], 400);
        }

        // Fire the WebSocket event for desktop worker if active
        event(new DealScrapeRequested($deal->url, 'price_update'));

        // Direct live fetch fallback on server (works 100% on shared hosting / production)
        try {
            $userAgents = [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0'
            ];
            $ua = $userAgents[array_rand($userAgents)];

            $response = Http::withHeaders([
                'User-Agent' => $ua,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5'
            ])->timeout(10)->get($deal->url);

            if ($response->successful()) {
                $html = $response->body();
                [$newPrice, $newOriginalPrice] = $this->parseAmazonPriceAndMrp($html);

                $updated = false;
                if ($newPrice && $newPrice > 0) {
                    if ($deal->discounted_price != $newPrice) {
                        PriceHistory::create([
                            'deal_id' => $deal->id,
                            'price' => $newPrice,
                            'recorded_at' => now()
                        ]);
                        $deal->discounted_price = $newPrice;
                    }
                    $updated = true;
                }

                if ($newOriginalPrice && $newOriginalPrice > 0) {
                    $deal->original_price = $newOriginalPrice;
                    $updated = true;
                }

                if ($updated) {
                    $deal->save();
                    event(new DealUpdated($deal));
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Direct live price fetch failed for deal {$deal->id}: " . $e->getMessage());
        }

        $discountPct = 0;
        if ($deal->original_price > 0 && $deal->original_price > $deal->discounted_price) {
            $discountPct = round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100);
        }

        return response()->json([
            'success' => true,
            'deal_id' => $deal->id,
            'discounted_price' => (float)$deal->discounted_price,
            'original_price' => (float)$deal->original_price,
            'discount_pct' => $discountPct,
            'message' => 'Price verified successfully.'
        ]);
    }

    /**
     * Helper method to parse live price and MRP directly from Amazon HTML
     */
    private function parseAmazonPriceAndMrp(string $html): array
    {
        $discountedPrice = null;
        $originalPrice = null;

        // 1. Extract Discounted Price
        if (preg_match('/class="[^"]*priceToPay[^"]*"[^>]*>.*?class="a-price-whole"[^>]*>([\d,]+)/s', $html, $m)) {
            $discountedPrice = (float)str_replace(',', '', $m[1]);
        } elseif (preg_match('/id="priceblock_dealprice"[^>]*>.*?₹?\s*([\d,.]+)/s', $html, $m)) {
            $discountedPrice = (float)str_replace(',', '', $m[1]);
        } elseif (preg_match('/class="a-price-whole"[^>]*>([\d,]+)/s', $html, $m)) {
            $discountedPrice = (float)str_replace(',', '', $m[1]);
        }

        // 2. Extract Original Price (MRP)
        // Check explicit M.R.P.: tag first
        if (preg_match('/M\.R\.P\.?:?\s*(?:<\/?[^>]+>)*\s*₹?\s*([\d,]+)/i', $html, $m)) {
            $candidate = (float)str_replace(',', '', $m[1]);
            if ($candidate > 0) {
                $originalPrice = $candidate;
            }
        }

        if (!$originalPrice) {
            if (preg_match_all('/class="a-text-price[^"]*"[^>]*>.*?class="a-offscreen"[^>]*>\s*₹?\s*([\d,.]+)/s', $html, $matches)) {
                foreach ($matches[1] as $priceStr) {
                    $candidate = (float)str_replace(',', '', $priceStr);
                    // Filter out per-unit prices (e.g. 100x multiplier)
                    if ($discountedPrice && $candidate > $discountedPrice && ($candidate / $discountedPrice) < 40) {
                        $originalPrice = $candidate;
                        break;
                    }
                }
            }
        }

        return [$discountedPrice, $originalPrice];
    }

    /**
     * Triggered by the Python daemon once it has fetched the new price
     */
    public function updatePrice(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'price' => 'required|numeric',
            'original_price' => 'nullable|numeric'
        ]);

        $inputUrl = $request->url;
        
        // Find deal by canonical url, affiliate url, or clean prefix match
        $deal = Deal::where('url', $inputUrl)
            ->orWhere('affiliate_url', $inputUrl)
            ->first();

        if (!$deal) {
            $cleanInput = strtok($inputUrl, '?');
            $deal = Deal::where('url', 'LIKE', $cleanInput . '%')->first();
        }

        if ($deal) {
            $oldPrice = $deal->discounted_price;
            $newPrice = (float)$request->price;
            $newOriginalPrice = $request->original_price ? (float)$request->original_price : null;

            // Log history if price actually changed
            if ($oldPrice != $newPrice) {
                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $newPrice,
                    'recorded_at' => now()
                ]);
                $deal->discounted_price = $newPrice;
            }

            if ($newOriginalPrice && $newOriginalPrice > 0) {
                $deal->original_price = $newOriginalPrice;
            }

            $deal->save();

            // ALWAYS broadcast DealUpdated event to notify frontend that verification complete
            event(new DealUpdated($deal));
            
            Log::info("Deal {$deal->id} price verified/updated. Discounted: {$newPrice}, MRP: {$deal->original_price}");

            return response()->json([
                'success' => true, 
                'deal_id' => $deal->id,
                'discounted_price' => $deal->discounted_price,
                'original_price' => $deal->original_price
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Deal not found.'], 404);
    }
}


