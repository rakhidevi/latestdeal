<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwitterApiService
{
    protected string $bearerToken;

    public function __construct(SocialAccount $account)
    {
        // For simple v2 posting, we might just use Bearer token or OAuth 1.0a
        // Here we assume OAuth 2.0 Bearer for simplicity
        $this->bearerToken = $account->access_token;
    }

    public function publishTweet(Deal $deal, string $tweetText): bool
    {
        $endpoint = "https://api.twitter.com/2/tweets";

        try {
            $res = Http::withToken($this->bearerToken)
                ->post($endpoint, [
                    'text' => $tweetText
                ]);

            if (!$res->successful()) {
                Log::error('Twitter Publish Error: ' . $res->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Twitter Publish Exception: ' . $e->getMessage());
            return false;
        }
    }
}
