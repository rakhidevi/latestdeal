<?php

namespace App\Services\AI\Adapters;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

abstract class BaseOpenAIAdapter implements ProviderAdapterInterface
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getProviderId(): string
    {
        return $this->config['id'];
    }

    public function isCapable(array $capabilities): bool
    {
        foreach ($capabilities as $cap) {
            $cap = strtoupper($cap);
            if ($cap === 'VISION' && empty($this->config['vision_model'])) {
                return false;
            }
            // Add other capability checks if needed based on provider config
        }
        return true;
    }

    public function chat(array $messages, array $options = []): array
    {
        $baseUrl = rtrim($this->config['base_url'], '/');
        if (!\Illuminate\Support\Str::endsWith($baseUrl, '/v1')) {
            // some providers include /v1 in base url, some don't
            // $baseUrl .= '/v1';
        }
        
        $endpoint = $baseUrl . '/chat/completions';
        $model = $this->config['model'];

        if (in_array('VISION', $options['capabilities'] ?? [])) {
            $model = $this->config['vision_model'];
        }

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        if (in_array('JSON', $options['capabilities'] ?? [])) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        if (isset($options['max_tokens'])) {
            $payload['max_tokens'] = $options['max_tokens'];
        }

        $request = Http::timeout($options['timeout'] ?? 30);
        
        if (!empty($this->config['api_key'])) {
            $request->withToken($this->config['api_key']);
        }

        $response = $request->post($endpoint, $payload);

        if ($response->failed()) {
            throw $response->toException();
        }

        $data = $response->json();
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception("Invalid response structure from " . $this->getProviderId());
        }

        return [
            'content' => $data['choices'][0]['message']['content'],
            'model' => $data['model'] ?? $model,
            'provider' => $this->getProviderId(),
        ];
    }

    public function listModels(): array
    {
        $baseUrl = rtrim($this->config['base_url'], '/');
        $endpoint = $baseUrl . '/models';

        $request = Http::timeout(10);
        if (!empty($this->config['api_key'])) {
            $request->withToken($this->config['api_key']);
        }

        $response = $request->get($endpoint);
        
        if ($response->failed()) {
            return [];
        }

        $data = $response->json('data') ?? [];
        return array_column($data, 'id');
    }

    public function normalizeError(\Throwable $e): array
    {
        if ($e instanceof RequestException) {
            $status = $e->response->status();
            if (in_array($status, [401, 403, 404])) {
                return ['type' => 'FAILOVER', 'message' => "HTTP {$status}: " . $e->getMessage()];
            }
            if (in_array($status, [408, 429, 500, 502, 503, 504])) {
                return ['type' => 'RETRY', 'message' => "HTTP {$status}: " . $e->getMessage()];
            }
            if ($status === 400) {
                // Often bad request means unsupported capability or model missing
                return ['type' => 'FAILOVER', 'message' => "HTTP 400: " . $e->getMessage()];
            }
        }

        if ($e instanceof \Illuminate\Http\Client\ConnectionException || str_contains($e->getMessage(), 'cURL error 28')) {
            return ['type' => 'RETRY', 'message' => 'Connection timeout or error: ' . $e->getMessage()];
        }

        return ['type' => 'FAILOVER', 'message' => 'Unknown error: ' . $e->getMessage()];
    }
}
