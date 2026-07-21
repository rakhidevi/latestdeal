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

        // Group by discounted_price and normalized title stem (strips emojis, spaces, 'new', hyphens)
        $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MIN(id)')
                ->from('deals')
                ->where('status', 'active')
                ->groupBy(
                    'discounted_price', 
                    DB::raw("SUBSTR(LOWER(REPLACE(REPLACE(REPLACE(REPLACE(title, '🚨', ''), 'new', ''), ' ', ''), '-', '')), 1, 15)")
                );
        });

        return $next($payload);
    }
}
