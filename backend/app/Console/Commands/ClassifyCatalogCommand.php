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
        'Home & Kitchen' => [
            '/ecoflow/i', '/power station/i', '/powerstation/i', '/inverter/i', '/battery/i', 
            '/wardrobe/i', '/bedroom/i', '/furniture/i', '/bniture/i', '/mattress/i', '/kurlon/i', 
            '/atomberg/i', '/fan/i', '/singer/i', '/sewing/i', '/delonghi/i', '/coffee/i', '/espresso/i', 
            '/kuvings/i', '/juicer/i', '/blender/i', '/dyson/i', '/vacuum/i', '/cleaner/i', '/chair/i', 
            '/desk/i', '/cellbell/i', '/air fryer/i', '/cooker/i'
        ],
        'Sports & Fitness' => [
            '/walking pad/i', '/treadmill/i', '/fitkit/i', '/cult/i', '/fitness/i', '/gym/i', 
            '/slovic/i', '/resistance band/i', '/exercise/i'
        ],
        'Fashion & Accessories' => [
            '/shirt/i', '/shoe/i', '/sneaker/i', '/t-shirt/i', '/jeans/i', '/jacket/i', '/bag/i', '/backpack/i'
        ],
        'Beauty & Personal Care' => [
            '/colorbot/i', '/hair/i', '/shampoo/i', '/serum/i', '/skincare/i', '/grooming/i', '/trimmer/i'
        ],
        'Courses & Education' => [
            '/course/i', '/udemy/i', '/python/i', '/bootcamp/i', '/tutorial/i', '/degree/i'
        ],
        'Gaming' => [
            '/gaming/i', '/playstation/i', '/ps5/i', '/xbox/i', '/nintendo/i', '/controller/i', '/console/i'
        ],
        'Electronics' => [
            '/iphone/i', '/ipad/i', '/macbook/i', '/airpods/i', '/sony/i', '/asus/i', '/acer/i', '/laptop/i', 
            '/monitor/i', '/tv/i', '/headphone/i', '/earphone/i', '/audio/i', '/soundbar/i', '/zebronics/i', 
            '/dash cam/i', '/70mai/i', '/xgimi/i', '/projector/i', '/sandisk/i', '/ssd/i', '/drive/i', '/smartwatch/i', 
            '/noise/i', '/boat/i', '/samsung/i', '/galaxy/i', '/apple/i'
        ],
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

        // 2. Classify ALL deals into accurate categories based on rules
        $dealsToClassify = Deal::all();
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

        // 3. Resolve and assign brands for all deals
        $resolver = app(\App\Services\Catalog\BrandResolver::class);
        $allDeals = Deal::all();
        $brandedCount = 0;
        foreach ($allDeals as $deal) {
            $resolver->resolveAndAssign($deal);
            $brandedCount++;
        }
        $this->info("Assigned brands for {$brandedCount} deals.");

        // Recount all deal_count fields
        app(\App\Services\Catalog\BrandCounter::class)->recountAll();
        app(\App\Services\NavigationVersionManager::class)->incrementVersion();

        return 0;
    }
}
