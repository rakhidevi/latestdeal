<?php

namespace App\Services;

use App\Models\Deal;
use App\Services\Catalog\BrandResolver;
use App\Services\Catalog\BrandCounter;
use App\Services\NavigationVersionManager;

class BrandSyncService
{
    protected BrandResolver $resolver;
    protected BrandCounter $counter;
    protected NavigationVersionManager $versionManager;

    public function __construct(
        BrandResolver $resolver,
        BrandCounter $counter,
        NavigationVersionManager $versionManager
    ) {
        $this->resolver = $resolver;
        $this->counter = $counter;
        $this->versionManager = $versionManager;
    }

    public function syncAllDeals(): array
    {
        $deals = Deal::all();
        $processed = 0;
        $needsReview = 0;

        foreach ($deals as $deal) {
            $brand = $this->resolver->resolveAndAssign($deal);
            if (!$brand || $deal->needs_brand_review) {
                $needsReview++;
            }
            $processed++;
        }

        $this->recalculateCounts();

        return [
            'processed' => $processed,
            'unknown' => $needsReview
        ];
    }

    public function recalculateCounts(): void
    {
        $this->counter->recountAll();
        $this->versionManager->incrementVersion();
    }
}
