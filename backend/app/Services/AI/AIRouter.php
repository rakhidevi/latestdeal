<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

class AIRouter
{
    protected ProviderRegistry $registry;
    protected CircuitBreaker $circuitBreaker;
    protected int $maxAttempts;
    protected int $maxRetries;

    public function __construct(ProviderRegistry $registry, CircuitBreaker $circuitBreaker)
    {
        $this->registry = $registry;
        $this->circuitBreaker = $circuitBreaker;
        $this->maxAttempts = (int) env('AI_MAX_TOTAL_ATTEMPTS', 6);
        $this->maxRetries = (int) env('AI_MAX_RETRIES_PER_PROVIDER', 1);
    }

    public function chat(array $messages, array $options = []): array
    {
        $capabilities = $options['capabilities'] ?? [];
        $order = $this->registry->getOrder();
        $adapters = $this->registry->getAdapters();

        $attemptCount = 0;

        foreach ($order as $providerId) {
            if (!isset($adapters[$providerId])) continue;
            
            $adapter = $adapters[$providerId];
            
            if (!$adapter->isCapable($capabilities)) {
                Log::debug("AI Router: Skipping {$providerId}, lacks requested capabilities.");
                continue;
            }

            $state = $this->circuitBreaker->getState($providerId);
            if ($state === CircuitBreaker::STATE_OPEN) {
                Log::debug("AI Router: Skipping {$providerId}, circuit is OPEN.");
                continue;
            }

            // Optional: Dynamic model discovery validate on startup. 
            // In a real app we might cache the valid models per provider. 
            // For now, assume model configured is valid and handle failures via catch.

            $retries = 0;
            
            while ($retries <= $this->maxRetries && $attemptCount < $this->maxAttempts) {
                $attemptCount++;
                try {
                    $response = $adapter->chat($messages, $options);

                    // Output structured check
                    if (in_array('JSON', $capabilities) || in_array('STRUCTURED', $capabilities)) {
                        $parsed = json_decode($response['content'], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception("Invalid JSON output from provider");
                        }
                    }

                    $this->circuitBreaker->recordSuccess($providerId);
                    return $response;

                } catch (\Throwable $e) {
                    $errorInfo = $adapter->normalizeError($e);
                    
                    if (str_contains($e->getMessage(), "Invalid JSON output")) {
                        Log::warning("AI Router: {$providerId} returned invalid JSON. Attempt {$retries}");
                        if ($retries < $this->maxRetries) {
                            $retries++;
                            // Repair hint
                            $messages[] = ['role' => 'user', 'content' => 'The previous response was not valid JSON. Please reply ONLY with valid JSON.'];
                            continue;
                        }
                        $this->circuitBreaker->recordFailure($providerId);
                        break; // Failover
                    }

                    Log::warning("AI Router: {$providerId} failed: " . $e->getMessage());

                    if ($errorInfo['type'] === 'RETRY' && $retries < $this->maxRetries) {
                        $retries++;
                        sleep(1 * $retries); // simple backoff
                        continue;
                    }

                    // Failover
                    $this->circuitBreaker->recordFailure($providerId);
                    break; 
                }
            }

            if ($attemptCount >= $this->maxAttempts) {
                break;
            }
        }

        throw new \Exception("AI Router: Exhausted all available providers or reached max attempts.");
    }
}
