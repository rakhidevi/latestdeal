# AI Fallback Strategy

**ID:** REQ-AI-003
**Status:** Completed
**Last Updated:** 2026-06-29

## The Problem
The local machine running the Ollama AI instance might be offline, powered down, or lose internet connectivity.

## The Fallback
If the server's queue has deals but hasn't received captions from the local worker within a timeout window, it will gracefully fallback to a hardcoded PHP string template:

```php
// Fallback Template
$caption = "🚨 {$deal->title} – " . round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100) . "% OFF 🖱️💙\n\n" .
           "💸 M.R.P.: ₹{$deal->original_price}\n" .
           "🔥 Deal Price: ₹{$deal->discounted_price}\n\n" .
           "⭐ 4.0/5 Rated\n\n" .
           "👉🏻 Buy Now: {$url}\n\n" .
           "✔️ High Quality\n✔️ Limited Time Offer\n\n" .
           "💎 LatestDeal.in Best Value – Don't miss out on this discount!";
```
This ensures the publishing engine never stops, even if AI is offline.
