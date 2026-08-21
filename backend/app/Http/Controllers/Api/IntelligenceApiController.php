<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Deal;
use Illuminate\Http\Request;

class IntelligenceApiController extends Controller
{
    public function checkExists(Request $request)
    {
        $url = $request->query('url');
        $asin = $request->query('asin');

        if (!$url && !$asin) {
            return response()->json(['error' => 'Provide url or asin'], 400);
        }

        $query = Deal::query();

        if ($url) {
            $query->orWhere('url', $url);
        }
        
        if ($asin) {
            // Assume ASIN might be in URL
            $query->orWhere('url', 'like', "%$asin%");
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }

    public function getCategories()
    {
        return response()->json(Category::select('id', 'name')->get());
    }

    public function getBrands()
    {
        return response()->json(Brand::select('id', 'name')->get());
    }
}
