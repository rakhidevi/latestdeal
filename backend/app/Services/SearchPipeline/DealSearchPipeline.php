<?php

namespace App\Services\SearchPipeline;

use Illuminate\Support\Facades\Pipeline;
use App\Models\Deal;

class DealSearchPipeline
{
    protected $pipes = [
        \App\Services\SearchPipeline\Pipes\ApplyDeduplication::class,
        \App\Services\SearchPipeline\Pipes\ApplyFilters::class,
        \App\Services\SearchPipeline\Pipes\ApplyRanking::class,
        \App\Services\SearchPipeline\Pipes\ApplyPagination::class,
    ];

    /**
     * Executes the search pipeline
     *
     * @param array $filters Request filters and parameters
     * @return mixed Paginator or Collection
     */
    public function search(array $filters)
    {
        $normalizer = new FilterNormalizer();
        $normalizedFilters = $normalizer->normalize($filters);

        $query = Deal::where('status', 'active');
        
        $payload = new SearchPayload($query, $normalizedFilters);

        $result = Pipeline::send($payload)
            ->through($this->pipes)
            ->then(function ($payload) {
                $res = $payload->getResult();
                
                if ($res instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                    $uniqueDeals = $res->getCollection()->unique(function ($deal) {
                        $cleanTitle = mb_strtolower($deal->title ?? '', 'UTF-8');
                        $cleanTitle = preg_replace('/(wireless|bluetooth|headphones|headphone|earphones|earphone|noise|cancelling|cancellation|reduction|new|on-ear|over-ear|in-ear|mic|3-level|adjustable|for|youtube|with|and|brown|midnight|blue|black|white|silver|grey|gold|red|color|1006834|🚨)/i', '', $cleanTitle);
                        $cleanTitle = preg_replace('/[^a-z0-9]/', '', $cleanTitle);
                        return $cleanTitle . '_' . (int)($deal->discounted_price ?? 0);
                    });
                    $res->setCollection($uniqueDeals->values());
                } elseif ($res instanceof \Illuminate\Support\Collection) {
                    $res = $res->unique(function ($deal) {
                        $cleanTitle = mb_strtolower($deal->title ?? '', 'UTF-8');
                        $cleanTitle = preg_replace('/(wireless|bluetooth|headphones|headphone|earphones|earphone|noise|cancelling|cancellation|reduction|new|on-ear|over-ear|in-ear|mic|3-level|adjustable|for|youtube|with|and|brown|midnight|blue|black|white|silver|grey|gold|red|color|1006834|🚨)/i', '', $cleanTitle);
                        $cleanTitle = preg_replace('/[^a-z0-9]/', '', $cleanTitle);
                        return $cleanTitle . '_' . (int)($deal->discounted_price ?? 0);
                    })->values();
                }

                return $res;
            });

        return $result;
    }
}
