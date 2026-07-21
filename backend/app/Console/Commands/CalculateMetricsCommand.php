<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Deal;

class CalculateMetricsCommand extends Command
{
    protected $signature = 'latestdeal:calculate-metrics';
    protected $description = 'Calculates intelligence metrics (average discount, trending score, etc.) for Brands and Categories based on active deals.';

    public function handle()
    {
        $this->info('Starting metrics calculation...');

        $this->calculateBrandMetrics();
        $this->calculateCategoryMetrics();

        $this->info('Metrics calculated successfully.');
        return 0;
    }

    protected function calculateBrandMetrics()
    {
        $this->info('Calculating Brand metrics...');

        $brands = Brand::where('is_active', true)->get();
        $bar = $this->output->createProgressBar(count($brands));

        $discountFormula = '((original_price - discounted_price) * 100.0 / original_price)';

        foreach ($brands as $brand) {
            $stats = Deal::where('brand_id', $brand->id)
                ->where('status', 'active')
                ->where('original_price', '>', 0)
                ->selectRaw("AVG($discountFormula) as avg_discount, AVG(ai_score) as avg_ai_score, COUNT(id) as deal_count")
                ->first();

            $avgDiscount = $stats->avg_discount ? round($stats->avg_discount, 2) : 0;
            $avgAiScore = $stats->avg_ai_score ? round($stats->avg_ai_score, 2) : 50;

            // Simple trending formula based on AI score, avg discount and deal volume
            $trendingScore = min(($avgDiscount * 0.5) + ($avgAiScore * 0.4) + ($stats->deal_count * 0.1), 100);

            $brand->update([
                'average_discount' => $avgDiscount,
                'trending_score' => round($trendingScore, 2),
                'product_count' => $stats->deal_count,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function calculateCategoryMetrics()
    {
        $this->info('Calculating Category metrics...');

        $categories = Category::all();
        $bar = $this->output->createProgressBar(count($categories));

        $discountFormula = '((original_price - discounted_price) * 100.0 / original_price)';

        foreach ($categories as $category) {
            $stats = Deal::where('category_id', $category->id)
                ->where('status', 'active')
                ->where('original_price', '>', 0)
                ->selectRaw("AVG($discountFormula) as avg_discount, AVG(ai_score) as avg_ai_score, COUNT(id) as deal_count")
                ->first();

            $avgDiscount = $stats->avg_discount ? round($stats->avg_discount, 2) : 0;
            $avgAiScore = $stats->avg_ai_score ? round($stats->avg_ai_score, 2) : 50;
            $trendingScore = min(($avgDiscount * 0.5) + ($avgAiScore * 0.4) + ($stats->deal_count * 0.1), 100);

            // Find top merchant for this category (merchant with most active deals in this category)
            $topMerchant = Deal::where('category_id', $category->id)
                ->where('status', 'active')
                ->select('merchant_id', DB::raw('COUNT(id) as deal_count'))
                ->groupBy('merchant_id')
                ->orderBy('deal_count', 'desc')
                ->first();

            $category->update([
                'average_discount' => $avgDiscount,
                'trending_score' => round($trendingScore, 2),
                'top_merchant_id' => $topMerchant ? $topMerchant->merchant_id : null,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
