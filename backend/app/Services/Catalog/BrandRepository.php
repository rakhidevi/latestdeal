<?php

namespace App\Services\Catalog;

use App\Models\Brand;
use App\Models\Deal;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

class BrandRepository
{
    /**
     * Find or create a brand by raw name, resolving slug collisions.
     */
    public function findOrCreateByName(string $name): Brand
    {
        $cleanName = trim($name);
        $slug = Str::slug($cleanName);
        if (empty($slug)) {
            $slug = 'brand-' . time();
        }

        // Check by exact slug first
        $brand = Brand::where('slug', $slug)->first();
        if ($brand) {
            return $brand;
        }

        // Check by case-insensitive name match
        $brand = Brand::whereRaw('LOWER(name) = ?', [strtolower($cleanName)])->first();
        if ($brand) {
            return $brand;
        }

        return Brand::create([
            'name' => $cleanName,
            'slug' => $slug,
            'is_active' => true,
            'deal_count' => 0
        ]);
    }

    /**
     * Find brand by slug
     */
    public function findBySlug(string $slug): ?Brand
    {
        return Brand::where('slug', $slug)->first();
    }

    /**
     * Search active brands by query string for auto-complete & server search.
     */
    public function searchBrands(string $query, int $limit = 20): Collection
    {
        $cleanQuery = trim($query);
        if (empty($cleanQuery)) {
            return $this->getTopBrands($limit);
        }

        return Brand::where('is_active', true)
            ->where('name', 'like', '%' . $cleanQuery . '%')
            ->orderBy('deal_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top active brands ordered by precomputed deal_count.
     */
    public function getTopBrands(int $limit = 20): Collection
    {
        return Brand::where('is_active', true)
            ->where('deal_count', '>', 0)
            ->orderBy('deal_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Merge a duplicate source brand into a target brand.
     */
    public function mergeBrands(int $sourceBrandId, int $targetBrandId): bool
    {
        $source = Brand::find($sourceBrandId);
        $target = Brand::find($targetBrandId);

        if (!$source || !$target) {
            return false;
        }

        // Reassign all deals from source to target
        Deal::where('brand_id', $source->id)->update([
            'brand_id' => $target->id,
            'brand' => $target->name
        ]);

        // Recalculate target count
        $target->deal_count = Deal::where('brand_id', $target->id)->where('status', 'active')->count();
        $target->saveQuietly();

        // Delete source brand
        $source->delete();

        return true;
    }
}
