<?php

namespace App\Services\Studio;

use App\Services\Studio\DTOs\SimulationResultDTO;

class PolicySimulatorService
{
    /**
     * Executes a given policy against an array of payloads in an isolated sandbox.
     */
    public function simulate(string $policyRules, array $payloads): SimulationResultDTO
    {
        // Sandbox environment execution simulation.
        // In reality, this might invoke an isolated rule engine or container.
        
        $passed = 0;
        $failed = 0;
        $totalLatency = 0;
        $sampleFailures = [];
        $samplePasses = [];

        foreach ($payloads as $index => $payload) {
            $start = microtime(true);
            
            // Simple string matching simulation for demonstration
            $isPass = true;
            $failureReason = null;
            
            // E.g., if policy dictates a minimum discount
            if (str_contains($policyRules, 'min_discount')) {
                // Parse rule
                preg_match('/min_discount:\s*([0-9]+)/', $policyRules, $matches);
                $minDiscount = isset($matches[1]) ? (int) $matches[1] : 0;
                
                $actualDiscount = $payload['discount_percentage'] ?? 0;
                if ($actualDiscount < $minDiscount) {
                    $isPass = false;
                    $failureReason = "Discount ({$actualDiscount}%) below minimum ({$minDiscount}%)";
                }
            }

            if (str_contains($policyRules, 'brand_allowed')) {
                $actualBrand = $payload['brand'] ?? 'Unknown';
                if ($actualBrand === 'BannedBrand') {
                    $isPass = false;
                    $failureReason = "Brand is banned.";
                }
            }

            $latency = (microtime(true) - $start) * 1000;
            $totalLatency += $latency;

            if ($isPass) {
                $passed++;
                if (count($samplePasses) < 5) {
                    $samplePasses[] = ['payload' => $payload, 'latency_ms' => round($latency, 2)];
                }
            } else {
                $failed++;
                if (count($sampleFailures) < 5) {
                    $sampleFailures[] = ['payload' => $payload, 'reason' => $failureReason, 'latency_ms' => round($latency, 2)];
                }
            }
        }

        $total = count($payloads);
        $passRate = $total > 0 ? ($passed / $total) * 100 : 0;
        $avgLatency = $total > 0 ? (int)($totalLatency / $total) : 0;

        return new SimulationResultDTO([
            'total_payloads_processed' => $total,
            'passed_count' => $passed,
            'failed_count' => $failed,
            'pass_rate_percentage' => round($passRate, 2),
            'average_latency_ms' => $avgLatency,
            'sample_failures' => $sampleFailures,
            'sample_passes' => $samplePasses
        ]);
    }
}
