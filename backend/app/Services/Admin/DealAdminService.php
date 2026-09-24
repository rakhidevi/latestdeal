<?php

namespace App\Services\Admin;

use App\Models\Deal;

class DealAdminService
{
    /**
     * Get deals with filtering and counts for the admin catalog.
     */
    public function getDealsCatalogData($status, $search, $sortBy = 'created_at')
    {
        $query = Deal::where('status', $status);
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('url', 'like', '%' . $search . '%');
            });
        }
        
        if ($sortBy === 'updated_at') {
            $query->orderBy('updated_at', 'desc');
        } elseif ($sortBy === 'discount') {
            $query->orderBy('discount_percentage', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        $deals = $query->paginate(20)->withQueryString();
        
        $counts = [
            'pending' => Deal::where('status', 'pending')->count(),
            'active' => Deal::where('status', 'active')->count(),
            'rejected' => Deal::where('status', 'rejected')->count(),
        ];

        $illegalCount = $this->countIllegalDeals();

        $uniqueDomains = \Illuminate\Support\Facades\Cache::remember('admin_catalog_domains', 300, function () {
            $merchantDomains = \App\Models\Merchant::whereNotNull('domain')->pluck('domain');
            if ($merchantDomains->isNotEmpty()) {
                return $merchantDomains->values();
            }
            return Deal::latest()->limit(100)->pluck('url')->map(function ($url) {
                return parse_url($url, PHP_URL_HOST);
            })->filter()->unique()->values();
        });

        return compact('deals', 'status', 'counts', 'search', 'sortBy', 'illegalCount', 'uniqueDomains');
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
        $payload = ['status' => $status];

        if ($status === 'active') {
            $payload['editorial_status'] = Deal::STATUS_PUBLISHED;
            $payload['reviewed_at'] = now();
            $payload['editor_id'] = auth()->id() ?? 1;

            if (empty($deal->editorial_summary)) {
                $payload['editorial_summary'] = $deal->title . ($deal->description ? ' - ' . \Illuminate\Support\Str::limit($deal->description, 200) : '');
            }
            if (empty($deal->editorial_verdict)) {
                $payload['editorial_verdict'] = "Verified deal offering significant savings on {$deal->title}. Recommended for value-conscious shoppers.";
            }
            if (empty($deal->pros) || !is_array($deal->pros)) {
                $payload['pros'] = ['Authentic merchant discount', 'Strong value for price'];
            }
            if (empty($deal->cons) || !is_array($deal->cons)) {
                $payload['cons'] = ['Limited time or stock availability'];
            }
        } elseif ($status === 'rejected') {
            $payload['editorial_status'] = Deal::STATUS_ARCHIVED;
        }

        return $deal->update($payload);
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
