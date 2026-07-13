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
            'price' => 'required|numeric'
        ]);

        $deal = Deal::where('url', $request->url)->first();

        if ($deal) {
            $oldPrice = $deal->discounted_price;
            $newPrice = $request->price;

            // Only update DB history if there's an actual change
            if ($oldPrice != $newPrice) {
                // Keep history
                PriceHistory::create([
                    'deal_id' => $deal->id,
                    'price' => $newPrice,
                    'recorded_at' => now()
                ]);

                $deal->discounted_price = $newPrice;
                $deal->save();
                
                Log::info("Deal {$deal->id} price updated in real-time from {$oldPrice} to {$newPrice}");
            }

            // ALWAYS broadcast to frontend so it can stop the loading spinner, even if price didn't change
            event(new DealUpdated($deal->id, $newPrice));

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Deal not found.'], 404);
    }
}
