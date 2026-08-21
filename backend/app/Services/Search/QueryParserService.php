<?php

namespace App\Services\Search;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductType;
use App\Services\Search\SearchQuery;

class QueryParserService
{
    /**
     * Parses a raw text string into a structured SearchQuery DTO.
     */
    public function parse(string $rawQuery): SearchQuery
    {
        $searchQuery = new SearchQuery($rawQuery);
        $remainingText = strtolower(" " . $rawQuery . " ");

        // 1. Parse Prices
        $remainingText = $this->extractPrices($remainingText, $searchQuery);

        // 2. Parse Discounts
        $remainingText = $this->extractDiscounts($remainingText, $searchQuery);

        // 3. Extract Taxonomy (Brands, Categories, Product Types)
        $remainingText = $this->extractTaxonomy($remainingText, $searchQuery);

        // 4. Residual Keywords
        $residual = trim(preg_replace('/\s+/', ' ', $remainingText));
        if (!empty($residual)) {
            $searchQuery->keywords = array_filter(explode(' ', $residual));
        }

        return $searchQuery;
    }

    private function extractPrices(string $text, SearchQuery $query): string
    {
        // "under 50000", "below 50000", "less than 50000", "upto 50000", "up to 50000", "< 50000", "under 50k", "50000 or less"
        
        $patterns = [
            '/(?:under|below|less than|upto|up to|<)\s*(?:rs\.?|inr|₹)?\s*([\d,]+(?:k)?)/i',
            '/(?:rs\.?|inr|₹)?\s*([\d,]+(?:k)?)\s*or less/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $valStr = str_replace(',', '', strtolower($matches[1]));
                $multiplier = 1;
                if (strpos($valStr, 'k') !== false) {
                    $multiplier = 1000;
                    $valStr = str_replace('k', '', $valStr);
                }
                
                $query->maxPrice = floatval($valStr) * $multiplier;
                
                // Remove the matched phrase from text
                $text = str_replace($matches[0], ' ', $text);
            }
        }
        
        return $text;
    }

    private function extractDiscounts(string $text, SearchQuery $query): string
    {
        // "60% off", "60 percent off", "discount above 60%", "60%+", "at least 60%", "more than 60%", "60%"
        
        // Match specific patterns first
        $patterns = [
            '/(?:discount above|at least|more than|minimum)\s*(\d+)\s*(?:%|percent)/i',
            '/(\d+)\s*(?:%|percent)\s*(?:off|discount|\+)/i',
            '/(\d+)\s*%/i' // Fallback to just "60%"
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $query->minDiscount = floatval($matches[1]);
                $text = str_replace($matches[0], ' ', $text);
                break; // Stop after first match to avoid parsing "50% off and 10% cashback" twice incorrectly
            }
        }

        return $text;
    }

    private function extractTaxonomy(string $text, SearchQuery $query): string
    {
        // Note: For large databases, loading all might be inefficient, 
        // but for <1000 brands/categories, it's very fast. We cache in memory.
        
        // Extract Brands
        $brands = Brand::where('is_active', true)->get();
        foreach ($brands as $brand) {
            $brandName = strtolower($brand->name);
            $pattern = '/\b' . preg_quote($brandName, '/') . '\b/i';
            if (preg_match($pattern, $text)) {
                $query->brandIds[] = $brand->id;
                $text = preg_replace($pattern, ' ', $text);
            }
        }

        // Extract Product Types (Most specific first)
        // E.g. "Running Shoes" matches before "Shoes"
        $productTypes = ProductType::orderByRaw('LENGTH(name) DESC')->get();
        foreach ($productTypes as $pt) {
            $ptName = strtolower($pt->name);
            $pattern = '/\b' . preg_quote($ptName, '/') . '\b/i';
            if (preg_match($pattern, $text)) {
                $query->productTypeIds[] = $pt->id;
                $text = preg_replace($pattern, ' ', $text);
            }
        }

        // Extract Categories
        $categories = Category::orderByRaw('LENGTH(name) DESC')->get();
        foreach ($categories as $cat) {
            $catName = strtolower($cat->name);
            $pattern = '/\b' . preg_quote($catName, '/') . '\b/i';
            if (preg_match($pattern, $text)) {
                $query->categoryIds[] = $cat->id;
                $text = preg_replace($pattern, ' ', $text);
            }
        }

        return $text;
    }
}
