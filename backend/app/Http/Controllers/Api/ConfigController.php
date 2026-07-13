<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    /**
     * Returns the dynamic configuration for the Python worker.
     */
    public function scrapingConfig()
    {
        $brands = DB::table('brand_tiers')->get();
        $scrapingConfigs = DB::table('scraping_configs')->where('is_active', true)->orderBy('priority', 'desc')->get();
        
        return response()->json([
            'brand_tiers' => $brands,
            'scraping_configs' => $scrapingConfigs
        ]);
    }
}
