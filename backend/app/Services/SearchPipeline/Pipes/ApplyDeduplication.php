<?php

namespace App\Services\SearchPipeline\Pipes;

use Closure;
use App\Services\SearchPipeline\SearchPayload;
use Illuminate\Support\Facades\DB;

class ApplyDeduplication
{
    public function handle(SearchPayload $payload, Closure $next)
    {
        $query = $payload->query;

        // Runtime deduplication: Select single representative deal per brand/price/category
        $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MIN(id)')
                ->from('deals')
                ->where('status', 'active')
                ->groupBy('discounted_price', DB::raw("COALESCE(brand_id, 0)"), 'category_id');
        });

        return $next($payload);
    }
}
