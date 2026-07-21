<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deal;
use App\Models\Category;
use App\Models\Merchant;
use Illuminate\Support\Str;

class ClassifyCatalogCommand extends Command
{
    protected $signature = 'deals:classify';
    protected $description = 'Consolidate duplicate merchants and reclassify deals in General category into rich specific categories.';

    protected array $categoryRules = [
        'Electronics' => ['/iphone/i', '/ipad/i', '/macbook/i', '/airpods/i', '/sony/i', '/playstation/i', '/ps5/i', '/asus/i', '/acer/i', '/laptop/i', '/monitor/i', '/tv/i', '/headphone/i', '/earphone/i', '/audio/i', '/dash cam/i', '/70mai/i', '/xgimi/i', '/projector/i', '/sandisk/i', '/ssd/i', '/drive/i', '/smartwatch/i'],
        'Home & Kitchen' => ['/atomberg/i', '/fan/i', '/singer/i', '/sewing/i', '/delonghi/i', '/coffee/i', '/espresso/i', '/kuvings/i', '/juicer/i', '/blender/i', '/mattress/i', '/kurlon/i', '/dyson/i', '/vacuum/i', '/cleaner/i', '/chair/i', '/desk/i', '/cellbell/i', '/air fryer/i', '/cooker/i'],
        'Fashion & Accessories' => ['/shirt/i', '/shoe/i', '/sneaker/i', '/t-shirt/i', '/jeans/i', '/jacket/i', '/slovic/i', '/resistance band/i', '/fitness/i'],
        'Beauty & Personal Care' => ['/colorbot/i', '/hair/i', '/shampoo/i', '/serum/i', '/skincare/i', '/grooming/i', '/trimmer/i'],
        'Courses & Education' => ['/course/i', '/udemy/i', '/python/i', '/bootcamp/i', '/tutorial/i', '/degree/i'],
        'Gaming' => ['/gaming/i', '/rog/i', '/tuf/i', '/controller/i', '/console/i']
    ];

    public function handle(): int
    {
        $this->info('Starting merchant consolidation...');

        // 1. Merge "Amazon India" into "Amazon"
        $mainAmazon = Merchant::where('name', 'Amazon')->first();
        if (!$mainAmazon) {
            $mainAmazon = Merchant::where('name', 'like', '%Amazon%')->first();
        }

        if ($mainAmazon) {
            $otherAmazons = Merchant::where('id', '!=', $mainAmazon->id)
                ->where(function($q) {
                    $q->where('name', 'like', '%Amazon%')
                      ->orWhere('domain', 'like', '%amazon%');
                })->get();

            foreach ($otherAmazons as $dup) {
                Deal::where('merchant_id', $dup->id)->update(['merchant_id' => $mainAmazon->id]);
                $this->info("Merged duplicate merchant {$dup->name} (ID {$dup->id}) into {$mainAmazon->name} (ID {$mainAmazon->id})");
                $dup->delete();
            }

            // Recalculate main Amazon count
            $mainAmazon->deal_count = Deal::where('merchant_id', $mainAmazon->id)->where('status', 'active')->count();
            $mainAmazon->saveQuietly();
        }

        $this->info('Starting deal category reclassification...');

        // 2. Classify deals currently in General or uncategorized
        $generalCat = Category::where('slug', 'general')->orWhere('name', 'General')->first();

        $query = Deal::query();
        if ($generalCat) {
            $query->where('category_id', $generalCat->id)->orWhereNull('category_id');
        } else {
            $query->whereNull('category_id');
        }

        $dealsToClassify = $query->get();
        $reclassified = 0;

        foreach ($dealsToClassify as $deal) {
            $title = $deal->title;
            $targetCategoryName = null;

            foreach ($this->categoryRules as $catName => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $title)) {
                        $targetCategoryName = $catName;
                        break 2;
                    }
                }
            }

            // Default to Electronics if tech terms present
            if (!$targetCategoryName) {
                $targetCategoryName = 'Electronics';
            }

            $slug = Str::slug($targetCategoryName);
            $category = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $targetCategoryName]
            );

            $deal->category_id = $category->id;
            $deal->saveQuietly();
            $reclassified++;
        }

        $this->info("Successfully reclassified {$reclassified} deals into specific categories.");

        // Recount all deal_count fields
        app(\App\Services\Catalog\BrandCounter::class)->recountAll();
        app(\App\Services\NavigationVersionManager::class)->incrementVersion();

        return 0;
    }
}
