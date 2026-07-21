<?php

namespace App\Services\Catalog;

use App\Models\Deal;
use App\Models\Brand;
use Illuminate\Support\Str;

class BrandResolver
{
    protected BrandRepository $repository;

    protected array $brandPatterns = [
        'Bose' => ['/\bbose\b/i', '/quietcomfort/i'],
        'Grenaro' => ['/\bgrenaro\b/i'],
        'OnePlus' => ['/\boneplus\b/i', '/nord\s+buds/i'],
        'Zebronics' => ['/\bzebronics\b/i', '/juke\s+bar/i'],
        'Apple' => ['/\bapple\b/i', '/iphone/i', '/ipad/i', '/macbook/i', '/airpods/i'],
        'Sony' => ['/\bsony\b/i', '/playstation/i', '/ps5/i'],
        'ASUS' => ['/\basus\b/i', '/tuf\s+gaming/i', '/\brog\b/i'],
        'Acer' => ['/\bacer\b/i'],
        'Samsung' => ['/\bsamsung\b/i', '/galaxy/i'],
        'Atomberg' => ['/atomberg/i'],
        'SHOKZ' => ['/shokz/i', '/openrun/i'],
        'CELLBELL' => ['/cellbell/i'],
        'SINGER' => ['/singer/i'],
        'DeLonghi' => ['/delonghi/i'],
        'Colorbot' => ['/colorbot/i'],
        'realme' => ['/\brealme\b/i'],
        '70mai' => ['/70mai/i'],
        'Kurlon' => ['/kurlon/i'],
        'Dyson' => ['/\bdyson\b/i'],
        'Slovic' => ['/slovic/i'],
        'XGIMI' => ['/xgimi/i'],
        'Kuvings' => ['/kuvings/i'],
        'LG' => ['/\blg\b/i'],
        'Lenovo' => ['/lenovo/i'],
        'HP' => ['/\bhp\b/i'],
        'Dell' => ['/\bdell\b/i'],
        'boAt' => ['/\bboat\b/i'],
        'Noise' => ['/\bnoise\b(?!\s+(cancelling|cancellation|reduction|canceling))/i'],
        'Xiaomi' => ['/xiaomi/i', '/redmi/i'],
        'Philips' => ['/philips/i'],
        'Whirlpool' => ['/whirlpool/i'],
        'Bosch' => ['/bosch/i'],
        'JBL' => ['/\bjbl\b/i'],
        'Sennheiser' => ['/sennheiser/i'],
        'SanDisk' => ['/sandisk/i'],
        'EF ECOFLOW' => ['/ecoflow/i'],
        'Fitkit' => ['/fitkit/i'],
        'Bniture' => ['/bniture/i'],
        'Udemy' => ['/udemy/i']
    ];

    public function __construct(BrandRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Resolve brand using priority chain:
     * 1. AI Extracted Brand
     * 2. Structured Merchant Data
     * 3. Existing Brand String
     * 4. Product Identifier (ASIN/SKU pattern)
     * 5. Title NLP Matching
     * 6. Fallback: NULL brand_id & set needs_brand_review = true
     */
    public function resolveAndAssign(Deal $deal): ?Brand
    {
        $detectedName = null;

        // 1. AI Extracted Metadata
        if (!empty($deal->ai_metadata) && is_array($deal->ai_metadata) && !empty($deal->ai_metadata['brand'])) {
            $detectedName = trim($deal->ai_metadata['brand']);
        }

        // 2. Title Pattern Matching (Highest Precision NLP Regex Rules)
        if (!$detectedName && !empty($deal->title)) {
            foreach ($this->brandPatterns as $brandName => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $deal->title)) {
                        $detectedName = $brandName;
                        break 2;
                    }
                }
            }
        }

        // 3. Legacy Brand Column Fallback
        if (!$detectedName && !empty($deal->brand) && strtolower(trim($deal->brand)) !== 'unknown brand') {
            $detectedName = trim($deal->brand);
        }

        if ($detectedName) {
            $brand = $this->repository->findOrCreateByName($detectedName);
            $deal->brand_id = $brand->id;
            $deal->brand = $brand->name;
            $deal->needs_brand_review = false;
            $deal->saveQuietly();
            return $brand;
        }

        // Unresolved: Set brand_id to NULL and flag for review (Do NOT create artificial "Unknown Brand" entity)
        $deal->brand_id = null;
        $deal->needs_brand_review = true;
        $deal->saveQuietly();

        return null;
    }
}
