<?php

namespace App\Services\AI\Adapters;

interface ProviderAdapterInterface
{
    public function getProviderId(): string;
    public function isCapable(array $capabilities): bool;
    public function chat(array $messages, array $options = []): array;
    public function listModels(): array;
    public function normalizeError(\Throwable $e): array;
}
