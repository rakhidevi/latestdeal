<?php

namespace App\Listeners;

use App\Services\Catalog\BrandResolver;

class BrandSyncListener
{
    protected BrandResolver $resolver;

    public function __construct(BrandResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function handle($event): void
    {
        $deal = $event->deal ?? null;
        if (!$deal) return;

        $this->resolver->resolveAndAssign($deal);
    }
}
