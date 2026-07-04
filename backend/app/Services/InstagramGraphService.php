<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramGraphService
{
    protected string $accessToken;
    protected string $instagramAccountId;

    public function __construct(SocialAccount $account)
    {
        $this->accessToken = $account->access_token;
        $this->instagramAccountId = $account->target_id; // Instagram Business Account ID
    }

    public function publishStory(Deal $deal): bool
    {
        // 1. Upload Media to Container (Story format)
        $endpointMedia = "https://graph.facebook.com/v19.0/{$this->instagramAccountId}/media";
        $imageUrl = url($deal->image_path); // Image needs to be publicly accessible

        try {
            $mediaRes = Http::post($endpointMedia, [
                'image_url' => $imageUrl,
                'media_type' => 'STORIES',
                'access_token' => $this->accessToken
            ]);

            if (!$mediaRes->successful()) {
                Log::error('IG Media Upload Error: ' . $mediaRes->body());
                return false;
            }

            $creationId = $mediaRes->json('id');

            // 2. Publish the Container
            $endpointPublish = "https://graph.facebook.com/v19.0/{$this->instagramAccountId}/media_publish";
            
            $publishRes = Http::post($endpointPublish, [
                'creation_id' => $creationId,
                'access_token' => $this->accessToken
            ]);

            if (!$publishRes->successful()) {
                Log::error('IG Media Publish Error: ' . $publishRes->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Instagram Publish Exception: ' . $e->getMessage());
            return false;
        }
    }
}
