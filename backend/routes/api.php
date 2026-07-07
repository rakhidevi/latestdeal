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
Route::post('/deals/ingest', [DealIngestionController::class, 'store']);
Route::post('/deals/{deal}/expire', [DealIngestionController::class, 'expire']);

// Shopper AI Engine
Route::post('/assistant/chat', [\App\Http\Controllers\Api\ShopperAssistantController::class, 'chat']);

// Protected APIs (Requires Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/deals/active', function () {
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
