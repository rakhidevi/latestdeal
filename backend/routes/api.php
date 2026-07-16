<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DealIngestionController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\MetricsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Deal Ingestion Engine (Called by local Python Worker)
Route::post('/deals/ingest', [\App\Http\Controllers\Api\DealIngestionController::class, 'store']);
Route::get('/deals/active', [\App\Http\Controllers\Api\DealIngestionController::class, 'activeDeals']);
Route::post('/deals/{deal}/expire', [\App\Http\Controllers\Api\DealIngestionController::class, 'expire']);

// Scraper Job Tracking
Route::post('/scraper/jobs', [\App\Http\Controllers\Api\ScraperJobController::class, 'store']);
Route::put('/scraper/jobs/{job}', [\App\Http\Controllers\Api\ScraperJobController::class, 'update']);

// Crawler Configuration
Route::get('/settings/crawlers', function () {
    return response()->json([
        'crawler_automated' => \App\Models\Setting::where('key', 'crawler_automated')->value('value') ?? 'enabled',
        'crawler_manual' => \App\Models\Setting::where('key', 'crawler_manual')->value('value') ?? 'enabled'
    ]);
});

// Shopper AI Engine
Route::post('/assistant/chat', [\App\Http\Controllers\Api\ShopperAssistantController::class, 'chat']);
Route::post('/predict-price', [\App\Http\Controllers\Api\PricePredictionController::class, 'predict']);
Route::get('/smart-search', [\App\Http\Controllers\Api\SmartSearchController::class, 'search']);

// Real-Time Price Verification
Route::post('/deals/{id}/refresh-price', [\App\Http\Controllers\Api\PriceUpdateController::class, 'refreshPrice']);
Route::post('/deals/update-price', [\App\Http\Controllers\Api\PriceUpdateController::class, 'updatePrice']);

// Protected APIs (Requires Bearer Token)
Route::group([], function () {
    Route::get('/deals/active', function (\Illuminate\Http\Request $request) {
        if (env('API_KEY') && $request->header('Authorization') !== 'Bearer ' . env('API_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return response()->json(['deals' => \App\Models\Deal::where('status', 'active')->get()]);
    });
    
    // Publisher Metrics
    Route::get('/publisher/metrics', [MetricsController::class, 'index']);
});

// Retention Engine (Public)
Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
Route::post('/alerts', [SubscriptionController::class, 'setAlert']);

// Webhooks
Route::post('/webhooks/telegram', [\App\Http\Controllers\Api\TelegramWebhookController::class, 'handle']);

// Chrome Extension API
Route::get('/deals/search', function (\Illuminate\Http\Request $request) {
    $q = $request->query('q');
    if (!$q) {
        return response()->json(['deals' => []]);
    }
    
    $words = explode(' ', $q);
    $query = \App\Models\Deal::query();
    
    $query->where(function($builder) use ($words) {
        foreach (array_slice($words, 0, 3) as $word) {
            if (strlen($word) > 2) {
                $builder->where('title', 'like', "%{$word}%");
            }
        }
    });
    
    $deals = $query->where('status', 'active')->orderBy('discounted_price', 'asc')->limit(1)->get();
    return response()->json(['deals' => $deals]);
});
