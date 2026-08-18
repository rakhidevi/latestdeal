<?php

namespace App\Services\AI;

use App\Services\AI\Adapters\NVIDIAAdapter;
use App\Services\AI\Adapters\OllamaAdapter;
use App\Services\AI\Adapters\GroqAdapter;
use App\Services\AI\Adapters\CerebrasAdapter;
use App\Services\AI\Adapters\ProviderAdapterInterface;

class ProviderRegistry
{
    protected array $adapters = [];
    protected array $order = [];

    public function __construct()
    {
        $this->loadConfiguration();
    }

    protected function loadConfiguration(): void
    {
        $orderEnv = env('AI_PROVIDER_ORDER', 'nvidia,ollama,groq,cerebras');
        $this->order = array_filter(array_map('trim', explode(',', $orderEnv)));

        foreach ($this->order as $providerKey) {
            $prefix = 'AI_' . strtoupper($providerKey) . '_';
            
            if (env($prefix . 'ENABLED', false)) {
                $config = [
                    'id' => $providerKey,
                    'base_url' => env($prefix . 'BASE_URL'),
                    'api_key' => env($prefix . 'API_KEY'),
                    'model' => env($prefix . 'MODEL'),
                    'vision_model' => env($prefix . 'VISION_MODEL'),
                ];

                $adapter = $this->createAdapter($providerKey, $config);
                if ($adapter) {
                    $this->adapters[$providerKey] = $adapter;
                }
            }
        }
    }

    protected function createAdapter(string $providerKey, array $config): ?ProviderAdapterInterface
    {
        return match (strtolower($providerKey)) {
            'nvidia' => new NVIDIAAdapter($config),
            'ollama' => new OllamaAdapter($config),
            'groq' => new GroqAdapter($config),
            'cerebras' => new CerebrasAdapter($config),
            default => null,
        };
    }

    public function getAdapters(): array
    {
        return $this->adapters;
    }

    public function getAdapter(string $id): ?ProviderAdapterInterface
    {
        return $this->adapters[$id] ?? null;
    }

    public function getOrder(): array
    {
        return $this->order;
    }
}
