<?php

namespace App\Services;

use App\Models\Deal;

class WhatsAppService
{
    /**
     * Generates a "Click-to-Share" Intent URL for WhatsApp since fully automated 
     * messaging is heavily restricted or expensive on WhatsApp.
     */
    public function generateShareIntent(Deal $deal): string
    {
        // Construct the Tracking URL (Redirect Engine)
        $trackingUrl = route('deal.redirect', ['deal' => $deal->hash_id]);

        // Format the share text
        $text = "🔥 *NEW DEAL ALERT* 🔥\n\n";
        $text .= $deal->title . "\n\n";
        $text .= "💰 Price: ₹" . $deal->discounted_price . " (was ₹" . $deal->original_price . ")\n\n";
        $text .= "👉 Buy Here: " . $trackingUrl . "\n";

        // URL encode the message for the wa.me intent
        $encodedText = urlencode($text);

        // Standard WhatsApp share intent
        return "https://wa.me/?text=" . $encodedText;
    }
}
