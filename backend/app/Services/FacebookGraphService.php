<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookGraphService
{
    protected string $accessToken;
    protected string $pageId;

    public function __construct(SocialAccount $account)
    {
        $this->accessToken = $account->access_token;
        $this->pageId = $account->target_id; // FB Page ID
    }

    public function publishPost(Deal $deal, string $caption): bool
    {
        $endpoint = "https://graph.facebook.com/v19.0/{$this->pageId}/photos";
        $imageUrl = url($deal->image_path);

        try {
            $res = Http::post($endpoint, [
                'url' => $imageUrl,
                'caption' => $caption,
                'access_token' => $this->accessToken
            ]);

            if (!$res->successful()) {
                Log::error('FB Publish Error: ' . $res->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('FB Publish Exception: ' . $e->getMessage());
            return false;
        }
    }
}
