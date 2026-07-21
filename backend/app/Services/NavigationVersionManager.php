<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class NavigationVersionManager
{
    protected const VERSION_KEY = 'navigation_tree_version';

    public function getCurrentVersion(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    public function getCacheKey(): string
    {
        return 'navigation:v' . $this->getCurrentVersion();
    }

    public function incrementVersion(): int
    {
        $newVersion = $this->getCurrentVersion() + 1;
        Cache::forever(self::VERSION_KEY, $newVersion);
        return $newVersion;
    }
}
