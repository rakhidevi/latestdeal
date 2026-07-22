<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\PriceHistory;
use App\Events\DealScrapeRequested;
use App\Events\DealUpdated;
use Illuminate\Support\Facades\Log;

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

        // Fire the WebSocket event that the Python daemon is listening to
        // Passing 'price_update' tells the daemon to skip AI and just grab the price
        event(new DealScrapeRequested($deal->url, 'price_update'));

        return response()->json([
            'success' => true,
            'message' => 'Price check initiated. Updating shortly...'
        ]);
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

