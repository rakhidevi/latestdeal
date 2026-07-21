<?php

namespace App\Listeners;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Merchant;
use App\Events\DealCreated;
use App\Events\DealDeleted;

class CatalogCounterListener
{
    public function handle($event): void
    {
        $deal = $event->deal ?? null;
        if (!$deal) return;

        $delta = ($event instanceof DealDeleted) ? -1 : 1;

        if ($deal->category_id) {
            $cat = Category::find($deal->category_id);
            if ($cat) {
                $cat->deal_count = max(0, $cat->deal_count + $delta);
                $cat->saveQuietly();
            }
        }

        if ($deal->brand_id) {
            $brand = Brand::find($deal->brand_id);
            if ($brand) {
                $brand->deal_count = max(0, $brand->deal_count + $delta);
                $brand->saveQuietly();
            }
        }

        if ($deal->merchant_id) {
            $merchant = Merchant::find($deal->merchant_id);
            if ($merchant) {
                $merchant->deal_count = max(0, $merchant->deal_count + $delta);
                $merchant->saveQuietly();
            }
        }
    }
}
