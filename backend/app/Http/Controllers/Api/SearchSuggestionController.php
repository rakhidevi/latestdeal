<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SearchSuggestionController extends Controller
{
    public function suggestions(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));
        if (strlen($query) < 2) {
            return response()->json([
                'brands' => [],
                'categories' => [],
                'merchants' => [],
                'deals' => []
            ]);
        }

        $hasBrandCount = \Illuminate\Support\Facades\Schema::hasColumn('brands', 'deal_count');
        $hasCatCount = \Illuminate\Support\Facades\Schema::hasColumn('categories', 'deal_count');
        $hasMercCount = \Illuminate\Support\Facades\Schema::hasColumn('merchants', 'deal_count');

        $brandCols = $hasBrandCount ? ['id', 'name', 'slug', 'deal_count'] : ['id', 'name', 'slug'];
        $catCols = $hasCatCount ? ['id', 'name', 'slug', 'deal_count'] : ['id', 'name', 'slug'];
        $mercCols = $hasMercCount ? ['id', 'name', 'deal_count'] : ['id', 'name'];

        $brands = Brand::where('name', 'like', "%{$query}%")
            ->where('is_active', true)
            ->take(4)
            ->get($brandCols)
            ->map(function ($b) {
                return [
                    'name' => $b->name,
                    'type' => 'Brand',
                    'url' => route('deals.brand', $b->slug),
                    'count' => ($b->deal_count ?? 0) . ' deals'
                ];
            });

        $categories = Category::where('name', 'like', "%{$query}%")
            ->where('slug', '!=', 'general')
            ->take(4)
            ->get($catCols)
            ->map(function ($c) {
                return [
                    'name' => $c->name,
                    'icon' => $c->icon,
                    'type' => 'Category',
                    'url' => route('deals.category', $c->slug),
                    'count' => ($c->deal_count ?? 0) . ' deals'
                ];
            });

        $merchants = Merchant::active()
            ->where('name', 'like', "%{$query}%")
            ->take(4)
            ->get($mercCols)
            ->map(function ($m) {
                return [
                    'name' => $m->name,
                    'icon' => $m->icon,
                    'type' => 'Merchant',
                    'url' => route('deals.merchant', Str::slug($m->name)),
                    'count' => ($m->deal_count ?? 0) . ' deals'
                ];
            });

        $deals = Deal::where('title', 'like', "%{$query}%")
            ->where('status', 'active')
            ->take(5)
            ->get(['id', 'title', 'slug', 'discounted_price', 'discount_percentage'])
            ->map(function ($d) {
                $price = $d->discounted_price ?? 0;
                return [
                    'name' => $d->title,
                    'type' => 'Deal',
                    'url' => route('deals.show', $d->slug),
                    'count' => '₹' . number_format($price) . ($d->discount_percentage ? ' (' . $d->discount_percentage . '% OFF)' : '')
                ];
            });

        return response()->json([
            'brands' => $brands,
            'categories' => $categories,
            'merchants' => $merchants,
            'deals' => $deals
        ]);
    }
}
