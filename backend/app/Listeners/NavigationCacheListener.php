<?php

namespace App\Listeners;

use App\Services\NavigationVersionManager;

class NavigationCacheListener
{
    protected NavigationVersionManager $versionManager;

    public function __construct(NavigationVersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
    }

    public function handle($event): void
    {
        $this->versionManager->incrementVersion();
    }
}
