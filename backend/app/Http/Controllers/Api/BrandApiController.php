<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Catalog\BrandRepository;

class BrandApiController extends Controller
{
    protected BrandRepository $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function search(Request $request)
    {
        $query = (string)$request->query('q', '');
        $limit = (int)$request->query('limit', 20);

        $brands = $this->brandRepository->searchBrands($query, $limit);

        return response()->json([
            'success' => true,
            'query' => $query,
            'count' => $brands->count(),
            'data' => $brands->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                    'deal_count' => $b->deal_count,
                    'url' => route('deals.brand', $b->slug)
                ];
            })
        ]);
    }
}
