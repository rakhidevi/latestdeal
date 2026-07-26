<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartSearchController extends Controller
{
    private array $stopwords = [
        'best', 'top', 'under', 'below', 'cheap', 'cheapest', 'deal', 'deals',
        'buy', 'show', 'find', 'list', 'me', 'with', 'for', 'less', 'than',
        'price', 'rs', 'rupees', 'inr', 'offer', 'offers', 'discount', 'discounts'
    ];

    public function search(Request $request)
    {
        $queryText = trim((string)$request->input('q'));
        
        if (empty($queryText)) {
            return response()->json(['deals' => [], 'ai_filters' => []]);
        }

        $filters = [];

        // 1. Native PHP regex fallback for price budgets (e.g. "under 30000", "below 15000", "under ₹50000")
        if (preg_match('/(?:under|below|less than|budget|max|<=|₹)\s*(\d+)/i', $queryText, $matches)) {
            $filters['max_price'] = (float)$matches[1];
        }

        // 2. Try Ollama AI for deep query intent if available (with fast 3s timeout)
        $ollamaBaseUrl = Setting::where('key', 'ollama_base_url')->value('value') ?? env('OLLAMA_BASE_URL', 'https://ai.latestdeal.in');
        
        if ($ollamaBaseUrl) {
            try {
                $ollamaUrl = rtrim($ollamaBaseUrl, '/') . '/api/generate';
                $model = Setting::where('key', 'ollama_model')->value('value') ?? env('OLLAMA_MODEL', 'llama3');

                $prompt = "You are an AI Search Query parser. Extract the intent from the following search query.\n" .
                          "Query: \"{$queryText}\"\n\n" .
                          "Reply ONLY with a raw JSON object containing these exact keys:\n" .
                          "{\n" .
                          "  \"category\": \"(string or null)\",\n" .
                          "  \"max_price\": (number or null),\n" .
                          "  \"keywords\": [\"array\", \"of\", \"meaningful\", \"keywords\"]\n" .
                          "}\nDo NOT include markdown formatting.";

                $response = Http::timeout(3)->post($ollamaUrl, [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'format' => 'json'
                ]);

                if ($response->successful() && $response->json('response')) {
                    $aiParsed = json_decode($response->json('response'), true);
                    if (is_array($aiParsed)) {
                        $filters = array_merge($filters, array_filter($aiParsed));
                    }
                }
            } catch (\Exception $e) {
                // Silently fallback to native PHP keyword parsing
            }
        }

        // 3. Extract clean keywords (removing stop words like 'best', 'under')
        $rawWords = preg_split('/\s+/', strtolower($queryText));
        $cleanKeywords = [];
        foreach ($rawWords as $w) {
            $wClean = preg_replace('/[^\w]/', '', $w);
            if (strlen($wClean) >= 3 && !in_array($wClean, $this->stopwords) && !is_numeric($wClean)) {
                $cleanKeywords[] = $wClean;
            }
        }

        if (empty($filters['keywords'])) {
            $filters['keywords'] = $cleanKeywords;
        }

        // 4. Build database query
        $query = Deal::where('status', 'active');
        
        if (!empty($filters['max_price']) && $filters['max_price'] > 0) {
            $query->where('discounted_price', '<=', $filters['max_price']);
        }
        
        if (!empty($filters['category'])) {
            $catName = (string)$filters['category'];
            $query->where(function($q) use ($catName) {
                $q->whereHas('category', function($cq) use ($catName) {
                    $cq->where('name', 'like', '%' . $catName . '%');
                })->orWhere('title', 'like', '%' . $catName . '%');
            });
        }
        
        if (!empty($filters['keywords'])) {
            $keywords = (array)$filters['keywords'];
            $query->where(function($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    if (strlen($keyword) >= 2) {
                        $q->orWhere('title', 'like', '%' . $keyword . '%')
                          ->orWhere('brand', 'like', '%' . $keyword . '%')
                          ->orWhere('features', 'like', '%' . $keyword . '%');
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
    }
}
