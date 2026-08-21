<?php

namespace App\Services\Admin;

use App\Models\Deal;

class DealAdminService
{
    /**
     * Get deals with filtering and counts for the admin catalog.
     */
    public function getDealsCatalogData($status, $search)
    {
        $query = Deal::where('status', $status);
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('url', 'like', '%' . $search . '%');
            });
        }
        
        $deals = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        $counts = [
            'pending' => Deal::where('status', 'pending')->count(),
            'active' => Deal::where('status', 'active')->count(),
            'rejected' => Deal::where('status', 'rejected')->count(),
        ];

        $illegalCount = $this->countIllegalDeals();

        $allUrls = Deal::pluck('url')->toArray();
        $uniqueDomains = collect($allUrls)->map(function ($url) {
            return parse_url($url, PHP_URL_HOST);
        })->filter()->unique()->values();

        return compact('deals', 'status', 'counts', 'search', 'illegalCount', 'uniqueDomains');
    }

    /**
     * Returns the count of deals matching blocked keywords.
     */
    public function countIllegalDeals(): int
    {
        $blockedKeywords = $this->getBlockedKeywords();

        $query = Deal::query();
        $query->where(function ($q) use ($blockedKeywords) {
            foreach ($blockedKeywords as $keyword) {
                $q->orWhere('title', 'like', '%' . $keyword . '%');
            }
        });

        return $query->count();
    }

    /**
     * Permanently deletes all deals matching blocked (illegal/pirated) keywords.
     */
    public function purgeIllegalDeals(): int
    {
        $blockedKeywords = $this->getBlockedKeywords();

        $query = Deal::query();
        $query->where(function ($q) use ($blockedKeywords) {
            foreach ($blockedKeywords as $keyword) {
                $q->orWhere('title', 'like', '%' . $keyword . '%');
            }
        });

        $count = $query->count();
        $query->delete();

        return $count;
    }

    public function updateDealStatus(Deal $deal, string $status): bool
    {
        return $deal->update(['status' => $status]);
    }

    public function destroyDeal(Deal $deal): ?bool
    {
        return $deal->delete();
    }

    private function getBlockedKeywords(): array
    {
        return [
            'mod apk', 'modded apk', 'cracked apk',
            'premium unlocked', 'unlocked all', 'pro unlocked',
            'no watermark', 'ad free mod', 'ads removed mod',
            'crack', 'cracked', 'keygen', 'serial key',
            'pirated', 'warez', 'nulled',
            'paid apk free', 'patched apk',
        ];
    }
}
