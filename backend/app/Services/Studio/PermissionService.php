<?php

namespace App\Services\Studio;

class PermissionService
{
    public function canAccessStudio(int $userId): bool
    {
        return true; // Simplified for now
    }

    public function canPerformAction(int $userId, string $action): bool
    {
        // e.g., 'TRIGGER_ROLLBACK', 'EDIT_KNOWLEDGE'
        return true;
    }
}
