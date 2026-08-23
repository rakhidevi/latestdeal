<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Search\QueryParserService;
use App\Services\Search\DealSearchService;

class SearchController extends Controller
{
    protected $parser;
    protected $searchService;

    public function __construct(QueryParserService $parser, DealSearchService $searchService)
    {
        $this->parser = $parser;
        $this->searchService = $searchService;
    }

    public function index(Request $request)
    {
        $rawQuery = $request->input('q', '');
        
        // 1. Natural Language Intent Parsing
        $searchQuery = $this->parser->parse($rawQuery);

        // 2. Explicit User Filters Override
        // If the user explicitly checks a filter on the sidebar, it adds to or overrides the parsed intent
        if ($request->has('brand')) {
            // For simplicity, assuming filter sends a single slug or comma-separated list
            // In a full implementation, we'd map brand slugs to IDs.
            // But since DealSearchService expects brandIds, we should resolve them.
            $brandSlugs = explode(',', $request->input('brand'));
            $brandIds = \App\Models\Brand::whereIn('slug', $brandSlugs)->pluck('id')->toArray();
            if (!empty($brandIds)) {
                $searchQuery->brandIds = array_unique(array_merge($searchQuery->brandIds, $brandIds));
            }
        }

        if ($request->has('category')) {
            $catSlugs = explode(',', $request->input('category'));
            $catIds = \App\Models\Category::whereIn('slug', $catSlugs)->pluck('id')->toArray();
            if (!empty($catIds)) {
                $searchQuery->categoryIds = array_unique(array_merge($searchQuery->categoryIds, $catIds));
            }
        }

        if ($request->has('store')) {
            $storeSlugs = explode(',', $request->input('store'));
            $merchantIds = \App\Models\Merchant::whereIn('slug', $storeSlugs)
                ->orWhereIn('name', $storeSlugs)
                ->pluck('id')->toArray();
            if (!empty($merchantIds)) {
                $searchQuery->merchantIds = array_unique(array_merge($searchQuery->merchantIds ?? [], $merchantIds));
            }
        }

        if ($request->has('discount')) {
            $searchQuery->minDiscount = (float) $request->input('discount');
        }

        if ($request->has('max_price')) {
            $searchQuery->maxPrice = (float) $request->input('max_price');
        }

        // 3. Search & Ranking
        $deals = $this->searchService->search($searchQuery);
        
        // Paginate results manually since DealSearchService returns a Collection
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 12;
        $paginatedDeals = new \Illuminate\Pagination\LengthAwarePaginator(
            $deals->forPage($page, $perPage),
            $deals->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // For rendering Chips, we need human-readable names for the parsed intent
        $parsedIntent = [
            'brands' => \App\Models\Brand::whereIn('id', $searchQuery->brandIds)->pluck('name')->toArray(),
            'categories' => \App\Models\Category::whereIn('id', $searchQuery->categoryIds)->pluck('name')->toArray(),
            'product_types' => \App\Models\ProductType::whereIn('id', $searchQuery->productTypeIds)->pluck('name')->toArray(),
            'discount' => $searchQuery->minDiscount,
            'maxPrice' => $searchQuery->maxPrice,
            'keywords' => $searchQuery->keywords
        ];

        return view('frontend.search', compact('paginatedDeals', 'searchQuery', 'parsedIntent', 'rawQuery'));
    }
}
