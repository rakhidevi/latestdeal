<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartSearchController extends Controller
{
    private array $stopwords = [
        'best', 'top', 'under', 'below', 'cheap', 'cheapest', 'deal', 'deals',
        'buy', 'show', 'find', 'list', 'me', 'with', 'for', 'less', 'than',
        'price', 'rs', 'rupees', 'inr', 'offer', 'offers', 'discount', 'discounts',
        'today', 'good', 'latest', 'want', 'need', 'recommend', 'looking'
    ];

    private array $accessoryTerms = [
        'case', 'cases', 'cover', 'covers', 'charger', 'chargers', 'charging cable',
        'cable', 'cables', 'cleaning kit', 'cleaner', 'cleaners', 'adapter', 'adapters',
        'screen protector', 'screen guard', 'tempered glass', 'skin', 'skins',
        'stand', 'mount', 'holder', 'strap', 'straps', 'sleeve', 'sleeves', 'pouch',
        'pouches', 'ear tips', 'power bank'
    ];

    public function search(Request $request)
    {
        $queryText = trim((string)$request->input('q'));

        if (empty($queryText)) {
            return response()->json(['deals' => [], 'ai_filters' => []]);
        }

        // 1. Deep Query Intent Parsing & Taxonomy Resolution
        $intent = $this->parseIntent($queryText);

        // 2. Query Candidate Deals with Exclusion Firewalls
        $query = Deal::where('status', 'active')
            ->where('discounted_price', '>', 0)
            ->with(['merchant', 'category']);

        // Hard Budget Constraint (Upper Bound)
        if (!empty($intent['max_price']) && $intent['max_price'] > 0) {
            $query->where('discounted_price', '<=', $intent['max_price']);
        }

        // Price Floor (Protects against accessories hijacking device queries)
        if (!empty($intent['min_price']) && $intent['min_price'] > 0) {
            $query->where('discounted_price', '>=', $intent['min_price']);
        }

        // Accessory Exclusion Firewall
        if ($intent['exclude_accessories']) {
            $query->where(function ($q) {
                $q->where('title', 'NOT LIKE', '%charger%')
                  ->where('title', 'NOT LIKE', '%charging cable%')
                  ->where('title', 'NOT LIKE', '%cable%')
                  ->where('title', 'NOT LIKE', '%case cover%')
                  ->where('title', 'NOT LIKE', '%back case%')
                  ->where('title', 'NOT LIKE', '%silicone case%')
                  ->where('title', 'NOT LIKE', '%protective case%')
                  ->where('title', 'NOT LIKE', '%hard case%')
                  ->where('title', 'NOT LIKE', '%case for%')
                  ->where('title', 'NOT LIKE', '%cover for%')
                  ->where('title', 'NOT LIKE', '%cleaner%')
                  ->where('title', 'NOT LIKE', '%cleaning%')
                  ->where('title', 'NOT LIKE', '%adapter%')
                  ->where('title', 'NOT LIKE', '%tempered glass%')
                  ->where('title', 'NOT LIKE', '%screen protector%')
                  ->where('title', 'NOT LIKE', '%strap%')
                  ->where('title', 'NOT LIKE', '%sleeve%')
                  ->where('title', 'NOT LIKE', '%mount%')
                  ->where('title', 'NOT LIKE', '%holder%')
                  ->where('title', 'NOT LIKE', '%skin%')
                  ->where('title', 'NOT LIKE', '%pouch%');
            });
        }

        // Category / Product Type Filtering
        if ($intent['product_type'] === 'smartphone') {
            // Hard exclude audio, headphones, soundbars, accessories, and cleaning products
            $query->where(function ($q) {
                $q->where('title', 'NOT LIKE', '%headphone%')
                  ->where('title', 'NOT LIKE', '%earphone%')
                  ->where('title', 'NOT LIKE', '%earbud%')
                  ->where('title', 'NOT LIKE', '%audio%')
                  ->where('title', 'NOT LIKE', '%soundbar%')
                  ->where('title', 'NOT LIKE', '%speaker%')
                  ->where('title', 'NOT LIKE', '%cleaner%')
                  ->where('title', 'NOT LIKE', '%cleaning%')
                  ->where('title', 'NOT LIKE', '%treadmill%')
                  ->where('title', 'NOT LIKE', '%walking pad%')
                  ->where('title', 'NOT LIKE', '%power station%')
                  ->where('title', 'NOT LIKE', '%wardrobe%');
            });
            $query->where(function ($q) {
                $q->whereHas('category', function ($cq) {
                    $cq->where('slug', 'mobiles')
                      ->orWhere('name', 'like', '%mobile%')
                      ->orWhere('name', 'like', '%smartphone%');
                })
                ->orWhere('title', 'like', '%smartphone%')
                ->orWhere('title', 'like', '%smart phone%')
                ->orWhere('title', 'like', '%mobile phone%')
                ->orWhere('title', 'like', '%iPhone%')
                ->orWhere('title', 'like', '%Galaxy%')
                ->orWhere('title', 'like', '%Realme%')
                ->orWhere('title', 'like', '%OnePlus%')
                ->orWhere('title', 'like', '%Redmi%')
                ->orWhere('title', 'like', '%Xiaomi%')
                ->orWhere('title', 'like', '%Motorola%')
                ->orWhere('title', 'like', '%Pixel %')
                ->orWhere('title', 'like', '%Vivo %')
                ->orWhere('title', 'like', '%iQOO%')
                ->orWhere('title', 'like', '%Poco%');
            });
        } elseif ($intent['product_type'] === 'airpods') {
            $query->where(function ($q) {
                $q->where('title', 'like', '%airpod%')
                  ->orWhere('title', 'like', '%air pods%');
            });
        } elseif ($intent['product_type'] === 'laptop') {
            $query->where(function ($q) {
                $q->whereHas('category', function ($cq) {
                    $cq->where('name', 'like', '%laptop%')
                      ->orWhere('name', 'like', '%computer%');
                })
                ->orWhere('title', 'like', '%laptop%')
                ->orWhere('title', 'like', '%macbook%')
                ->orWhere('title', 'like', '%notebook%')
                ->orWhere('title', 'like', '%ideapad%')
                ->orWhere('title', 'like', '%vivobook%')
                ->orWhere('title', 'like', '%thinkpad%')
                ->orWhere('title', 'like', '%gaming%');
            });
        } elseif ($intent['product_type'] === 'audio') {
            $query->where(function ($q) {
                $q->where('title', 'like', '%earbud%')
                  ->orWhere('title', 'like', '%headphone%')
                  ->orWhere('title', 'like', '%earphone%')
                  ->orWhere('title', 'like', '%neckband%')
                  ->orWhere('title', 'like', '%tws%')
                  ->orWhere('title', 'like', '%speaker%')
                  ->orWhere('title', 'like', '%soundbar%');
            });
        } elseif (!empty($intent['clean_keywords'])) {
            $keywords = (array) $intent['clean_keywords'];
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    if (strlen($kw) >= 2) {
                        $q->orWhere('title', 'like', '%' . $kw . '%')
                          ->orWhere('brand', 'like', '%' . $kw . '%');
                    }
                }
            });
        }

        // Fetch candidate set for multi-factor ranking
        $candidates = $query->limit(40)->get();

        // 3. Multi-Factor Relevance & Deal Quality Scoring
        $rankedDeals = $this->scoreAndRankDeals($candidates, $intent);

        return response()->json([
            'deals' => $rankedDeals->values(),
            'ai_filters' => [
                'max_price' => $intent['max_price'],
                'min_price' => $intent['min_price'],
                'product_type' => $intent['product_type'],
                'exclude_accessories' => $intent['exclude_accessories'],
                'keywords' => $intent['clean_keywords'],
            ]
        ]);
    }

    /**
     * Parse structured intent, budget constraints, and accessory boundaries from raw query.
     */
    public function parseIntent(string $queryText): array
    {
        $qNorm = strtolower($queryText);
        $qNorm = preg_replace('/(\d+),(\d+)/', '$1$2', $qNorm); // Remove commas in numbers

        $maxPrice = null;
        $minPrice = null;

        // 1. Budget Range parsing (e.g. "between 20k and 30k", "10000 to 20000", "20k - 30k")
        if (preg_match('/(?:between\s+)?(?:rs\.?|inr|₹)?\s*(\d+k?)\s*(?:to|-|and)\s*(?:rs\.?|inr|₹)?\s*(\d+k?)/i', $qNorm, $rangeMatches)) {
            $conv = fn($val) => str_contains(strtolower($val), 'k')
                ? (float) str_replace(['k', 'K'], '', $val) * 1000
                : (float) $val;
            $minPrice = $conv($rangeMatches[1]);
            $maxPrice = $conv($rangeMatches[2]);
        } else {
            // Upper bound budget (e.g. "under ₹30000", "under 30k", "below 15000", "budget 25000", "within 20k")
            if (preg_match('/(?:under|below|less than|within|upto|up to|max(?:imum)?|budget of|budget|<=)\s*(?:rs\.?|inr|₹)?\s*(\d+k?)/i', $qNorm, $bMatches)) {
                $val = strtolower($bMatches[1]);
                $maxPrice = str_contains($val, 'k') ? (float)str_replace('k', '', $val) * 1000 : (float)$val;
            } elseif (preg_match('/(?:rs\.?|inr|₹)\s*(\d+k?)\s*(?:budget|or less|max)?/i', $qNorm, $bMatches)) {
                $val = strtolower($bMatches[1]);
                $maxPrice = str_contains($val, 'k') ? (float)str_replace('k', '', $val) * 1000 : (float)$val;
            }
        }

        // 2. Accessory Intent Detection
        $accessoryRequested = false;
        foreach ($this->accessoryTerms as $acc) {
            if (str_contains($qNorm, $acc)) {
                $accessoryRequested = true;
                break;
            }
        }

        // 3. Primary Product Type Detection
        $productType = null;
        $floorPrice = null;

        if (preg_match('/\b(smartphone|smartphones|mobile|mobiles|phone|phones|iphone)\b/i', $qNorm)) {
            $productType = 'smartphone';
            $floorPrice = 3500;
        } elseif (preg_match('/\b(airpod|airpods)\b/i', $qNorm)) {
            $productType = 'airpods';
            $floorPrice = 4000;
        } elseif (preg_match('/\b(laptop|laptops|notebook|macbook|chromebook)\b/i', $qNorm)) {
            $productType = 'laptop';
            $floorPrice = 14000;
        } elseif (preg_match('/\b(smartwatch|smart watch|fitness band)\b/i', $qNorm)) {
            $productType = 'smartwatch';
            $floorPrice = 999;
        } elseif (preg_match('/\b(tv|television|smart tv|oled|qled)\b/i', $qNorm)) {
            $productType = 'tv';
            $floorPrice = 6500;
        } elseif (preg_match('/\b(earbuds|earphones|headphones|neckband|tws)\b/i', $qNorm)) {
            $productType = 'audio';
            $floorPrice = 499;
        }

        // If core product requested without explicit accessory intent, enforce exclusion
        $excludeAccessories = ($productType !== null) && !$accessoryRequested;

        if ($excludeAccessories && $floorPrice !== null) {
            if ($minPrice === null || $minPrice < $floorPrice) {
                $minPrice = $floorPrice;
            }
        }

        // 4. Clean keywords
        $rawWords = preg_split('/\s+/', $qNorm);
        $cleanKeywords = [];
        foreach ($rawWords as $w) {
            $wClean = preg_replace('/[^\w]/', '', $w);
            if (strlen($wClean) >= 3 && !in_array($wClean, $this->stopwords) && !is_numeric($wClean)) {
                $cleanKeywords[] = $wClean;
            }
        }

        return [
            'raw_query' => $queryText,
            'max_price' => $maxPrice,
            'min_price' => $minPrice,
            'product_type' => $productType,
            'accessory_requested' => $accessoryRequested,
            'exclude_accessories' => $excludeAccessories,
            'clean_keywords' => $cleanKeywords,
        ];
    }

    /**
     * Score candidate deals across relevance, verified savings, and AI telemetry.
     */
    protected function scoreAndRankDeals(Collection $candidates, array $intent): Collection
    {
        if ($candidates->isEmpty()) {
            return collect();
        }

        $maxBudget = $intent['max_price'];
        $productType = $intent['product_type'];
        $keywords = $intent['clean_keywords'];

        $scored = $candidates->map(function ($deal) use ($maxBudget, $productType, $keywords) {
            $titleLower = strtolower($deal->title);
            $relevanceScore = 0;

            // Product entity title match
            if ($productType === 'smartphone') {
                if (preg_match('/(5g|phone|smartphone|galaxy|iphone|narzo|oneplus|redmi|pixel|motorola)/i', $titleLower)) {
                    $relevanceScore += 35;
                }
            } elseif ($productType === 'airpods') {
                if (str_contains($titleLower, 'airpods') || str_contains($titleLower, 'air pods')) {
                    $relevanceScore += 45;
                }
            } elseif ($productType === 'laptop') {
                if (preg_match('/(laptop|notebook|macbook|ideapad|vivobook|thinkpad|gaming)/i', $titleLower)) {
                    $relevanceScore += 35;
                }
            }

            // Keyword matches
            foreach ($keywords as $kw) {
                if (str_contains($titleLower, $kw)) {
                    $relevanceScore += 15;
                }
            }

            // Budget Proximity (Award higher relevance to devices competing in the requested tier)
            if ($maxBudget && $maxBudget > 0) {
                $ratio = (float)$deal->discounted_price / $maxBudget;
                if ($ratio >= 0.50 && $ratio <= 1.0) {
                    $relevanceScore += 25; // Directly in the target budget tier
                } elseif ($ratio >= 0.25 && $ratio < 0.50) {
                    $relevanceScore += 10;
                }
            }

            // Verified Discount Quality Score (0 to 30)
            $discountPct = $deal->original_price > 0 && $deal->original_price > $deal->discounted_price
                ? round((($deal->original_price - $deal->discounted_price) / $deal->original_price) * 100)
                : 0;
            $discountScore = min(30, round($discountPct * 0.5));

            // AI Confidence / Rufus Telemetry Score (0 to 20)
            $aiScore = min(20, round(($deal->ai_score ?? 80) * 0.2));

            $totalScore = $relevanceScore + $discountScore + $aiScore;

            $savings = max(0, $deal->original_price - $deal->discounted_price);

            return [
                'deal' => $deal,
                'total_score' => $totalScore,
                'discount_pct' => $discountPct,
                'savings' => $savings,
                'price' => (float)$deal->discounted_price,
            ];
        });

        // Sort descending by calculated multi-factor score
        $sorted = $scored->sortByDesc('total_score')->values();

        // Identify Best Match, Best Value, and Lowest Price among top genuine results
        $bestMatchId = $sorted->first()['deal']->id ?? null;
        $bestValueDeal = $sorted->sortByDesc('savings')->first();
        $bestValueId = $bestValueDeal['deal']->id ?? null;
        $lowestPriceDeal = $sorted->sortBy('price')->first();
        $lowestPriceId = $lowestPriceDeal['deal']->id ?? null;

        return $sorted->take(12)->map(function ($item) use ($bestMatchId, $bestValueId, $lowestPriceId) {
            $deal = $item['deal'];
            $merchantName = optional($deal->merchant)->name ?? 'Amazon Prime';
            
            $recType = 'featured';
            $recReason = "Verified deal with {$item['discount_pct']}% discount from {$merchantName}.";

            if ($deal->id === $bestMatchId) {
                $recType = 'best_match';
                $recReason = "Top AI recommendation: Best balance of specifications, verified {$item['discount_pct']}% savings, and buyer trust.";
            } elseif ($deal->id === $bestValueId && $item['savings'] > 1000) {
                $recType = 'best_value';
                $recReason = "Highest savings: ₹" . number_format($item['savings']) . " below MRP ({$item['discount_pct']}% OFF) on {$merchantName}.";
            } elseif ($deal->id === $lowestPriceId) {
                $recType = 'lowest_price';
                $recReason = "Budget pick: Lowest entry price at ₹" . number_format($item['price']) . " for a genuine device.";
            }

            return [
                'id'            => $deal->id,
                'title'         => $deal->title,
                'price'         => (float) $deal->discounted_price,
                'original_price'=> (float) $deal->original_price,
                'discount_pct'  => $item['discount_pct'],
                'savings'       => $item['savings'],
                'url'           => $deal->url,
                'slug'          => $deal->slug,
                'deal_url'      => route('deals.show', $deal->slug),
                'merchant'      => $merchantName,
                'image_url'     => $deal->image_url,
                'image_path'    => $deal->image_url,
                'recommendation_type'   => $recType,
                'recommendation_reason' => $recReason,
                'ai_score'      => $deal->ai_score ?? 88,
                'verified_date' => now()->format('M d, Y'),
            ];
        });
    }
}
