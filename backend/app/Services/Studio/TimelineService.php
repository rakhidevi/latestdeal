<?php

namespace App\Services\Studio;

class TimelineService
{
    /**
     * Reconstructs a complete event graph for a given Trace ID.
     * Nodes and Edges are returned to support branching (Replay, Rollback).
     */
    public function reconstruct(string $traceId): array
    {
        // 1. Query the UCDP Immutable Event Stream for this traceId.
        // 2. Sort by: Occurred At -> Sequence Number -> Event UUID.
        // 3. Build EventNode and EventEdge graph.
        
        // Simulated graph reconstruction
        $nodes = [
            [
                'id' => 'node-1',
                'type' => 'DISCOVERY',
                'name' => 'Discovery Started',
                'timestamp' => '2023-11-01T09:10:22.000Z',
                'duration_ms' => 12,
                'worker' => 'Discovery Worker 2',
                'status' => 'success',
                'metrics' => [
                    'Started' => '09:10:22.000',
                    'Finished' => '09:10:22.012',
                    'Duration' => '12ms',
                    'Worker' => 'Discovery-01',
                    'Retry Count' => 0,
                    'Memory' => '45MB',
                    'Processing Time' => '10ms'
                ],
                'payload' => ['provider' => 'Amazon', 'strategy' => 'MRP Error']
            ],
            [
                'id' => 'node-2',
                'type' => 'EXTRACTION',
                'name' => 'Extracted Deal',
                'timestamp' => '2023-11-01T09:10:22.980Z',
                'duration_ms' => 980,
                'worker' => 'Extraction Worker 1',
                'status' => 'success',
                'metrics' => [
                    'Started' => '09:10:22.020',
                    'Finished' => '09:10:23.000',
                    'Duration' => '980ms',
                    'Worker' => 'Extractor-14',
                    'Retry Count' => 0,
                    'Browser' => 'Chromium-Headless',
                    'Processing Time' => '950ms'
                ],
                'payload' => ['price' => 1200, 'mrp' => 2500]
            ],
            [
                'id' => 'node-3',
                'type' => 'DECISION',
                'name' => 'Opportunity Scored',
                'timestamp' => '2023-11-01T09:10:23.033Z',
                'duration_ms' => 8,
                'worker' => 'Decision Engine Node',
                'status' => 'success',
                'metrics' => [
                    'Started' => '09:10:23.025',
                    'Finished' => '09:10:23.033',
                    'Duration' => '8ms'
                ],
                'payload' => ['overall_score' => 94, 'outcome' => 'PUBLISH']
            ],
            [
                'id' => 'node-4-replay',
                'type' => 'REPLAY',
                'name' => 'Policy Replay',
                'timestamp' => '2023-11-01T09:15:00.000Z',
                'duration_ms' => 15,
                'worker' => 'Replay Engine',
                'status' => 'warning',
                'metrics' => [
                    'Started' => '09:15:00.000',
                    'Finished' => '09:15:00.015',
                    'Duration' => '15ms'
                ],
                'payload' => ['policy_version' => 'v5.0.0', 'outcome' => 'REJECT']
            ]
        ];

        $edges = [
            ['source' => 'node-1', 'target' => 'node-2'],
            ['source' => 'node-2', 'target' => 'node-3'],
            ['source' => 'node-3', 'target' => 'node-4-replay', 'label' => 'Manual Replay']
        ];

        return [
            'trace_id' => $traceId,
            'nodes' => $nodes,
            'edges' => $edges
        ];
    }
}
