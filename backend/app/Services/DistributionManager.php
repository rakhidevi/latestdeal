<?php

namespace App\Services;

use App\Models\Deal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\PublishDealToTelegramJob;
use Intervention\Image\Facades\Image; // Assuming Intervention Image is used for card generation

class DistributionManager
{
    /**
     * Distribute a validated deal to all configured marketing channels.
     * Only triggers for active/approved deals with high 'publishability' or ROI.
     */
    public function distribute(Deal $deal, array $metrics = [])
    {
        Log::info("DistributionManager: Initiating distribution for Deal ID {$deal->id}");

        // 1. Generate Social Card
        $socialCardPath = $this->generateSocialCard($deal, $metrics);

        // 2. Telegram Broadcast (High Priority)
        $this->broadcastToTelegram($deal, $socialCardPath);

        // 3. WhatsApp Broadcast (Highest Conversion)
        $this->broadcastToWhatsApp($deal, $socialCardPath, $metrics);
        
        // 4. Other channels (Twitter, Instagram, etc)
        // ...
        
        return true;
    }

    protected function generateSocialCard(Deal $deal, array $metrics)
    {
        // Mocking the generation of a beautiful image card overlaying Price, Drop, and Bank Offers.
        $cardName = 'social_card_' . $deal->id . '.jpg';
        $cardPath = public_path('deals/' . $cardName);
        
        // In a real implementation:
        // $img = Image::make(public_path($deal->image_path));
        // $img->text('Deal of the Day!', 50, 50, function($font) { ... });
        // $img->save($cardPath);
        
        Log::info("DistributionManager: Generated Social Card at {$cardPath}");
        return 'deals/' . $cardName;
    }

    protected function broadcastToTelegram(Deal $deal, $imagePath)
    {
        // Re-using existing job logic, but routing through the manager
        Log::info("DistributionManager: Dispatching Telegram Job for Deal {$deal->id}");
        PublishDealToTelegramJob::dispatch($deal)->delay(now()->addSeconds(10));
    }

    protected function broadcastToWhatsApp(Deal $deal, $imagePath, array $metrics)
    {
        // WhatsApp API implementation
        $apiUrl = config('services.whatsapp.api_url', 'https://api.whatsapp.com/v1/messages');
        $token = config('services.whatsapp.token');
        
        if (!$token) {
            Log::warning("DistributionManager: WhatsApp Token not configured. Skipping WhatsApp broadcast.");
            return;
        }

        $effectivePrice = $metrics['effective_price'] ?? $deal->discounted_price;
        $mrp = $deal->original_price;
        $discount = $mrp > 0 ? round((($mrp - $effectivePrice) / $mrp) * 100) : 0;
        
        $message = "🚨 *Loot Deal Detected!*\n\n";
        $message .= "*{ $deal->title }*\n\n";
        $message .= "📉 MRP: ~₹{$mrp}~\n";
        $message .= "🔥 *Effective Price: ₹{$effectivePrice}* ({$discount}% OFF!)\n\n";
        $message .= "👉 Buy Now: {$deal->url}\n\n";
        $message .= "_Prices change fast. Check Bank Offers for max discount._";

        try {
            // Mock HTTP Call
            Log::info("DistributionManager: Simulated WhatsApp Broadcast via Utility: \n" . $message);
        } catch (\Exception $e) {
            Log::error("DistributionManager: WhatsApp Broadcast failed - " . $e->getMessage());
        }
    }
}
