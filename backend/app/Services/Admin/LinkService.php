<?php

namespace App\Services\Admin;

use App\Models\Merchant;
use Illuminate\Support\Facades\Log;

class LinkService
{
    public function generateTrackedUrl(string $url, $merchantId, ?string $subId): ?string
    {
        if (!$merchantId) {
            return null;
        }

        $merchant = Merchant::find($merchantId);
        if (!$merchant) {
            return null;
        }

        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return null;
        }

        $queryParams = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
        }

        // Strip previous affiliate or tracking parameters
        if ($merchant->affiliate_param_key) {
            unset($queryParams[$merchant->affiliate_param_key]);
        }
        unset($queryParams['sub1'], $queryParams['subId1'], $queryParams['subid'], $queryParams['affExtParam1'], $queryParams['ascsubtag']);

        // Set affiliate tag
        if ($merchant->affiliate_param_key && $merchant->store_id) {
            $queryParams[$merchant->affiliate_param_key] = $merchant->store_id;
        }

        // Set network-appropriate subId
        if ($subId) {
            $domain = strtolower($merchant->domain ?? '');
            if (str_contains($domain, 'amazon')) {
                $queryParams['ascsubtag'] = $subId;
            } elseif (str_contains($domain, 'flipkart')) {
                $queryParams['affExtParam1'] = $subId;
            } else {
                $queryParams['sub1'] = $subId;
            }
        }

        $scheme   = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : 'https://';
        $host     = $parsed['host'] ?? '';
        $port     = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path     = $parsed['path'] ?? '/';
        $query    = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

        return "{$scheme}{$host}{$port}{$path}{$query}{$fragment}";
    }
}
