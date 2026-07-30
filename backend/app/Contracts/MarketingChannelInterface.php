<?php

namespace App\Contracts;

interface MarketingChannelInterface
{
    /**
     * Dispatch a campaign via this channel.
     */
    public function dispatch($campaign, $recipients): bool;

    /**
     * Get the plugin manifest declaring capabilities.
     */
    public function getManifest(): array;
}
