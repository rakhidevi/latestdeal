<?php

namespace App\Services\Catalog;

use App\Models\Deal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * DealIngestionService
 * 
 * Central gate for all deal creation and updates.
 * Enforces:
 *   1. No duplicate deals (by URL hash)
 *   2. No 100% off / invalid pricing deals
 *   3. Price drop → update + bump deal to top of feed (price_bumped_at = now())
 *   4. Price increase → update silently, no bump
 */
class DealIngestionService
{
    /**
     * Ingest a deal. Creates it if new, updates if existing.
     * Returns ['action' => 'created'|'updated'|'price_dropped'|'price_increased'|'rejected', 'deal' => Deal|null, 'reason' => string]
     */
    public function ingest(array $data): array
    {
        // ── Guard 1: URL is required ──
        if (empty($data['url'])) {
            return ['action' => 'rejected', 'deal' => null, 'reason' => 'Missing URL'];
        }

        $urlHash = md5(trim($data['url']));
        $data['url_hash'] = $urlHash;

        // ── Guard 2: Reject invalid pricing (100% off, negative, free) ──
        $originalPrice  = (float)($data['original_price'] ?? 0);
        $discountedPrice = (float)($data['discounted_price'] ?? 0);

        if ($originalPrice <= 0 || $discountedPrice <= 0) {
            return ['action' => 'rejected', 'deal' => null, 'reason' => "Invalid price: original={$originalPrice}, discounted={$discountedPrice}"];
        }

        if ($discountedPrice >= $originalPrice) {
            return ['action' => 'rejected', 'deal' => null, 'reason' => "Discounted price ≥ original price (0% or negative discount). original={$originalPrice}, discounted={$discountedPrice}"];
        }

        $discountPct = (($originalPrice - $discountedPrice) / $originalPrice) * 100;
        if ($discountPct >= 100) {
            return ['action' => 'rejected', 'deal' => null, 'reason' => "100% or more off deals are not allowed. discount={$discountPct}%"];
        }

        // ── Guard 3: Compute slug if not provided ──
        if (empty($data['slug'])) {
            $baseSlug = Str::slug($data['title'] ?? 'deal');
            $data['slug'] = $this->uniqueSlug($baseSlug);
        }

        // ── Check for existing deal by URL hash ──
        $existing = Deal::where('url_hash', $urlHash)->first();

        if (!$existing) {
            // Brand new deal — create it
            $deal = Deal::create($data);
            return ['action' => 'created', 'deal' => $deal, 'reason' => 'New deal created'];
        }

        // ── Existing deal found — check price change ──
        $oldPrice = (float)$existing->discounted_price;
        $newPrice = $discountedPrice;

        $updateData = $data;
        unset($updateData['url'], $updateData['url_hash'], $updateData['slug']); // Don't overwrite key fields

        if ($newPrice < $oldPrice) {
            // Price DROPPED — update + bump to top
            $updateData['price_bumped_at'] = now();
            $existing->fill($updateData)->save();

            return [
                'action' => 'price_dropped',
                'deal' => $existing,
                'reason' => "Price dropped from ₹{$oldPrice} → ₹{$newPrice}. Bumped to top."
            ];
        } elseif ($newPrice > $oldPrice) {
            // Price INCREASED — update silently, no bump
            $existing->fill($updateData)->save();

            return [
                'action' => 'price_increased',
                'deal' => $existing,
                'reason' => "Price increased from ₹{$oldPrice} → ₹{$newPrice}. Updated silently."
            ];
        } else {
            // Price unchanged — still update metadata silently
            $existing->fill($updateData)->save();

            return [
                'action' => 'updated',
                'deal' => $existing,
                'reason' => "No price change. Metadata updated."
            ];
        }
    }

    /**
     * Generate a unique slug, appending the deal ID suffix if taken.
     */
    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $counter = 1;

        while (Deal::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
