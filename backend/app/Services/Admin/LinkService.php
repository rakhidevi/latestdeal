<?php

namespace App\Services\Admin;

use App\Models\Merchant;
use Illuminate\Support\Facades\Log;

class LinkService
{
    public function generateTrackedUrl(string $url, $merchantId, ?string $subId): ?string
    {
        $merchant = null;

        if ($merchantId) {
            $merchant = Merchant::find($merchantId);
        }

        if (!$merchant) {
            return null;
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        $trackedUrl = $url . $separator . $merchant->affiliate_param_key . '=' . $merchant->store_id;

        if ($subId) {
            $trackedUrl .= '&sub1=' . urlencode($subId);
        }

        return $trackedUrl;
    }
}
