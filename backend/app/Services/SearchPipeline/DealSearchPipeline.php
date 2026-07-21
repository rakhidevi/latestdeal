<?php

namespace App\Services\SearchPipeline;

use Illuminate\Support\Facades\Pipeline;
use App\Models\Deal;

class DealSearchPipeline
{
    protected $pipes = [
        \App\Services\SearchPipeline\Pipes\ApplyFilters::class,
        \App\Services\SearchPipeline\Pipes\ApplyRanking::class,
        \App\Services\SearchPipeline\Pipes\ApplyPagination::class,
        // Later we can add SEO and Analytics pipes here
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
                return $payload->getResult();
            });

        return $result;
    }
}
