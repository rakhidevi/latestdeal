<?php

namespace App\Services\Studio;

use Illuminate\Support\Facades\Cache;

class WorkspaceManager
{
    public function getUserLayout(int $userId, string $workspaceName): array
    {
        return Cache::remember("workspace_{$userId}_{$workspaceName}", 3600, function () {
            // Default layout
            return [
                'columns' => 3,
                'widgets' => []
            ];
        });
    }

    public function saveUserLayout(int $userId, string $workspaceName, array $layout): void
    {
        Cache::put("workspace_{$userId}_{$workspaceName}", $layout, 3600);
        // TODO: Persist to database eventually
    }
}
