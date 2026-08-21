<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Search\QueryParserService;
use App\Services\Search\DealSearchService;

class SearchApiController extends Controller
{
    protected $parser;
    protected $searchEngine;

    public function __construct(QueryParserService $parser, DealSearchService $searchEngine)
    {
        $this->parser = $parser;
        $this->searchEngine = $searchEngine;
    }

    public function search(Request $request)
    {
        $queryText = $request->query('q', '');
        
        if (empty(trim($queryText))) {
            return response()->json([
                'parsed_query' => null,
                'deals' => []
            ]);
        }

        // 1. Parse text into SearchQuery DTO
        $searchQuery = $this->parser->parse($queryText);

        // 2. Execute structured + fulltext search & rank results
        $rankedDeals = $this->searchEngine->search($searchQuery);

        return response()->json([
            'parsed_query' => [
                'brand_ids' => $searchQuery->brandIds,
                'category_ids' => $searchQuery->categoryIds,
                'product_type_ids' => $searchQuery->productTypeIds,
                'min_discount' => $searchQuery->minDiscount,
                'max_price' => $searchQuery->maxPrice,
                'keywords' => $searchQuery->keywords,
                'original' => $searchQuery->originalQuery
            ],
            'deals' => $rankedDeals
        ]);
    }
}
