<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SmartSearchController extends Controller
{
    public function search(Request $request)
    {
        $queryText = $request->input('q');
        
        if (empty($queryText)) {
            return response()->json(['deals' => []]);
        }

        $ollamaBaseUrl = Setting::where('key', 'ollama_base_url')->value('value') ?? env('OLLAMA_BASE_URL', 'https://ai.latestdeal.in');
        $ollamaUrl = rtrim($ollamaBaseUrl, '/') . '/api/generate';
        $model = Setting::where('key', 'ollama_model')->value('value') ?? env('OLLAMA_MODEL', 'llama3');

        $prompt = "You are an AI Search Query parser. Extract the intent from the following shopping search query.\n" .
                  "Query: \"{$queryText}\"\n\n" .
                  "Reply ONLY with a raw JSON object containing these exact keys. Use null if not found:\n" .
                  "{\n" .
                  "  \"category\": \"(string) E.g. Smartphone, Laptop, Earbuds, TV, etc.\",\n" .
                  "  \"max_price\": (number) E.g. 30000,\n" .
                  "  \"keywords\": [\"array\", \"of\", \"important\", \"keywords\"]\n" .
                  "}\n" .
                  "Do NOT include markdown formatting like ```json, just the raw JSON brackets.";

        try {
            $response = Http::timeout(15)->post($ollamaUrl, [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json'
            ]);

            $filters = [];
            if ($response->successful()) {
                $jsonString = $response->json('response');
                $filters = json_decode($jsonString, true) ?? [];
            }
            
            // Now apply filters to the database
            $query = Deal::where('status', 'active');
            
            if (!empty($filters['max_price'])) {
                $query->where('discounted_price', '<=', $filters['max_price']);
            }
            
            if (!empty($filters['category'])) {
                // Approximate category matching
                $query->whereHas('category', function($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['category'] . '%');
                });
            }
            
            if (!empty($filters['keywords'])) {
                $query->where(function($q) use ($filters) {
                    foreach($filters['keywords'] as $keyword) {
                        if (strlen($keyword) > 2) {
                            $q->orWhere('title', 'like', '%' . $keyword . '%')
                              ->orWhere('description', 'like', '%' . $keyword . '%')
                              ->orWhere('brand', 'like', '%' . $keyword . '%');
                        }
                    }
                });
            }
            
            // If AI failed or returned empty filters, fallback to standard full-text search
            if (empty($filters['max_price']) && empty($filters['category']) && empty($filters['keywords'])) {
                $words = explode(' ', $queryText);
                $query->where(function($q) use ($words) {
                    foreach (array_slice($words, 0, 3) as $word) {
                        if (strlen($word) > 2) {
                            $q->where('title', 'like', "%{$word}%");
                        }
                    }
                });
            }

            $deals = $query->orderBy('discounted_price', 'asc')->limit(12)->get()->map(function ($deal) {
                return [
                    'id'            => $deal->id,
                    'title'         => $deal->title,
                    'price'         => (float) $deal->discounted_price,
                    'original_price'=> (float) $deal->original_price,
                    'discount_pct'  => $deal->original_price > 0
                        ? round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100)
                        : 0,
                    'url'           => $deal->url,
                    'merchant'      => optional($deal->merchant)->name ?? 'Marketplace',
                    'image_path'    => $deal->image_path,
                ];
            });
            
            return response()->json([
                'deals' => $deals,
                'ai_filters' => $filters
            ]);

        } catch (\Exception $e) {
            Log::error("Smart Search failed: " . $e->getMessage());
            // Fallback to basic search
            $words = explode(' ', $queryText);
            $deals = Deal::where('status', 'active')
                ->where(function($q) use ($words) {
                    foreach (array_slice($words, 0, 3) as $word) {
                        if (strlen($word) > 2) {
                            $q->where('title', 'like', "%{$word}%");
                        }
                    }
                })
                ->orderBy('discounted_price', 'asc')->limit(12)->get();
                
            return response()->json(['deals' => $deals]);
        }
    }
}
