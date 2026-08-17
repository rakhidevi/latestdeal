<?php

namespace App\Services\Studio;

class TraceSearchService
{
    /**
     * Resolves any arbitrary user input into a list of canonical Trace IDs.
     * Supports UUIDs, ASINs, FSNs, correlation IDs, provider names, strategies, etc.
     */
    public function resolve(string $query): array
    {
        $query = trim($query);
        $results = [];

        // 1. UUID Resolution (e.g. target-123, opportunity-456, exact trace id)
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $query) || str_contains($query, '-')) {
            // Simulated lookup in UCDP entity index
            // If it's a trace_id, return it directly.
            // If it's an opportunity_uuid, find its parent trace.
            $results[] = [
                'trace_id' => 'trace-' . substr(md5($query), 0, 12),
                'match_type' => 'Exact UUID',
                'entity' => $query,
            ];
            return $results;
        }

        // 2. Provider Product ID (ASIN / FSN)
        // ASINs are typically 10 character alphanumeric, uppercase. FSNs are similar.
        if (preg_match('/^[A-Z0-9]{10,12}$/', strtoupper($query))) {
            // Simulated lookup for all traces associated with this product ID
            $results[] = [
                'trace_id' => 'trace-asin-1',
                'match_type' => 'ASIN / FSN',
                'entity' => strtoupper($query),
            ];
            $results[] = [
                'trace_id' => 'trace-asin-2',
                'match_type' => 'ASIN / FSN',
                'entity' => strtoupper($query),
            ];
            return $results;
        }

        // 3. Metadata Match (Provider, Strategy, Policy)
        // Simulated text search against recent active traces
        $results[] = [
            'trace_id' => 'trace-meta-999',
            'match_type' => 'Metadata Search',
            'entity' => $query,
        ];

        return $results;
    }
}
