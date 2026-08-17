<?php

namespace App\Services\Studio;

use App\Services\Studio\DTOs\PipelineNodeDTO;
use App\Services\Studio\DTOs\EventQueryDTO;

class PipelineService
{
    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Aggregates the raw events fetched by EventService into a macro-level pipeline graph.
     * @return PipelineNodeDTO[]
     */
    public function getPipelineNodes(string $timeframe = '1h'): array
    {
        $from = $this->parseTimeframe($timeframe);
        
        $query = new EventQueryDTO([
            'limit' => 1000, // Large enough to simulate an aggregation batch
            'from' => $from ? $from->toIsoString() : null
        ]);

        $events = $this->eventService->getEvents($query);
        return $this->aggregateEventsToPipeline($events);
    }

    private function parseTimeframe(string $timeframe)
    {
        return match ($timeframe) {
            '15m' => now()->subMinutes(15),
            '1h' => now()->subHour(),
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            'live' => now()->subMinutes(5), // Assume 'live' aggregates the last 5 mins
            default => null,
        };
    }

    private function aggregateEventsToPipeline(array $events): array
    {
        // 1. Group events by their category representing stages
        $stages = [
            'DISCOVERY' => ['name' => 'Target Discovery', 'events' => []],
            'QUEUE' => ['name' => 'Message Queue', 'events' => []],
            'EXTRACTION' => ['name' => 'DOM Extraction', 'events' => []],
            'VALIDATION' => ['name' => 'Policy Validation', 'events' => []],
            'EVIDENCE' => ['name' => 'Evidence Math', 'events' => []],
            'DECISION' => ['name' => 'Opportunity Decision', 'events' => []],
            'COMPATIBILITY' => ['name' => 'Compatibility Layer', 'events' => []],
            'PUBLISHING' => ['name' => 'Publishing Engine', 'events' => []],
            'REVENUE' => ['name' => 'Revenue Tracking', 'events' => []],
        ];

        foreach ($events as $event) {
            $cat = strtoupper($event['category']);
            if (isset($stages[$cat])) {
                $stages[$cat]['events'][] = $event;
            }
        }

        $nodes = [];
        foreach ($stages as $stageId => $stageData) {
            $nodes[] = $this->calculateMetricsForStage($stageId, $stageData['name'], $stageData['events']);
        }

        return $nodes;
    }

    private function calculateMetricsForStage(string $stageId, string $displayName, array $events): PipelineNodeDTO
    {
        if (empty($events)) {
            return new PipelineNodeDTO([
                'stage' => $stageId,
                'display_name' => $displayName,
                'status' => 'OFFLINE',
                'trend' => 'stable'
            ]);
        }

        $total = count($events);
        $successCount = 0;
        $failCount = 0;
        $totalLatency = 0;
        $lastFailedTraceId = null;

        foreach ($events as $event) {
            if (in_array($event['level'], ['ERROR', 'CRITICAL'])) {
                $failCount++;
                if (!$lastFailedTraceId) {
                    $lastFailedTraceId = $event['trace_id'];
                }
            } else {
                $successCount++;
            }
            
            $latency = $event['payload']['processing_time_ms'] ?? 0;
            $totalLatency += $latency;
        }

        $successRate = ($successCount / $total) * 100;
        $avgLatency = $total > 0 ? (int)($totalLatency / $total) : 0;

        // Dynamic Status Computation
        $status = 'HEALTHY';
        if ($successRate < 90) $status = 'WARNING';
        if ($successRate < 80) $status = 'CRITICAL';

        // Trend calculation (simulated based on latency vs threshold)
        $trend = 'stable';
        if ($avgLatency > 300) $trend = 'degrading';
        if ($avgLatency < 50 && $successRate > 98) $trend = 'improving';

        return new PipelineNodeDTO([
            'stage' => $stageId,
            'display_name' => $displayName,
            'status' => $status,
            'success_rate' => number_format($successRate, 1) . '%',
            'failure_rate' => number_format((100 - $successRate), 1) . '%',
            'queue_depth' => rand(0, 500), // Simulated
            'average_latency_ms' => $avgLatency,
            'active_workers' => rand(2, 20), // Simulated
            'events_processed' => $total,
            'events_failed' => $failCount,
            'trend' => $trend,
            'last_failed_trace_id' => $lastFailedTraceId,
        ]);
    }
}
