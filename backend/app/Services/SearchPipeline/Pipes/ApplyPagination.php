<?php

namespace App\Services\SearchPipeline\Pipes;

use Closure;
use App\Services\SearchPipeline\SearchPayload;

class ApplyPagination
{
    public function handle(SearchPayload $payload, Closure $next)
    {
        $filters = $payload->filters;
        $perPage = $filters['per_page'] ?? 15;

        // Eager load relationships to prevent N+1 queries
        $payload->query->with(['merchant', 'brand', 'category']);

        $payload->result = $payload->query->paginate($perPage)->withQueryString();

        return $next($payload);
    }
}
