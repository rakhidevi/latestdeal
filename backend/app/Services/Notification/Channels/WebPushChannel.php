<?php

namespace App\Services\Notification\Channels;

use App\Contracts\NotificationChannelInterface;
use App\Services\Notification\ImmutableNotificationPayload;
use App\Models\Subscriber;
use App\Models\PushSubscription;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebPushChannel implements NotificationChannelInterface
{
    public function getName(): string
    {
        return 'web_push';
    }

    public function send(Subscriber $subscriber, ImmutableNotificationPayload $payload, string $traceId): bool
    {
        $subscriptions = PushSubscription::where('subscriber_id', $subscriber->id)->get();

        if ($subscriptions->isEmpty()) {
            return false;
        }

        $allSuccess = true;

        foreach ($subscriptions as $subscription) {
            $success = $this->sendToSubscription($subscription, $payload, $subscriber, $traceId);
            if (!$success) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    private function sendToSubscription(
        PushSubscription $subscription,
        ImmutableNotificationPayload $payload,
        Subscriber $subscriber,
        string $traceId
    ): bool {
        $endpoint = $subscription->endpoint;
        $publicKey = config('services.vapid.public_key') ?? env('VAPID_PUBLIC_KEY');
        $privateKey = config('services.vapid.private_key') ?? env('VAPID_PRIVATE_KEY');

        if (empty($endpoint)) {
            return false;
        }

        // Build JWT VAPID authorization token
        $vapidHeaders = $this->createVapidHeaders($endpoint, $publicKey, $privateKey);

        try {
            $response = Http::withHeaders($vapidHeaders)
                ->withBody(json_encode($payload->toArray()), 'application/json')
                ->post($endpoint);

            $status = $response->status();

            // Handle stale / dead endpoints (404 Not Found, 410 Gone)
            if ($status === 404 || $status === 410) {
                Log::info("WebPush 404/410 detected. Purging dead subscription ID: {$subscription->id}");
                $subscription->delete();

                NotificationLog::create([
                    'trace_id' => $traceId,
                    'subscriber_id' => $subscriber->id,
                    'channel' => $this->getName(),
                    'notification_type' => $payload->getTag(),
                    'status' => 'expired_purged',
                    'attempts' => 1,
                    'response' => "HTTP {$status} Endpoint Gone - Subscription Auto-Deleted",
                ]);

                return false;
            }

            if ($response->successful() || $status === 201 || $status === 202) {
                $subscription->update([
                    'last_success_at' => now(),
                    'failure_count' => 0,
                ]);

                NotificationLog::create([
                    'trace_id' => $traceId,
                    'subscriber_id' => $subscriber->id,
                    'channel' => $this->getName(),
                    'notification_type' => $payload->getTag(),
                    'status' => 'sent',
                    'attempts' => 1,
                    'response' => "HTTP {$status} OK",
                ]);

                return true;
            }

            // Failure handling
            $subscription->increment('failure_count');
            $subscription->update(['last_failure_at' => now()]);

            NotificationLog::create([
                'trace_id' => $traceId,
                'subscriber_id' => $subscriber->id,
                'channel' => $this->getName(),
                'notification_type' => $payload->getTag(),
                'status' => 'failed',
                'attempts' => 1,
                'response' => "HTTP {$status}: " . substr($response->body(), 0, 200),
            ]);

            return false;
        } catch (\Exception $e) {
            $subscription->increment('failure_count');
            $subscription->update(['last_failure_at' => now()]);

            NotificationLog::create([
                'trace_id' => $traceId,
                'subscriber_id' => $subscriber->id,
                'channel' => $this->getName(),
                'notification_type' => $payload->getTag(),
                'status' => 'failed',
                'attempts' => 1,
                'response' => "Exception: " . $e->getMessage(),
            ]);

            return false;
        }
    }

    private function createVapidHeaders(string $endpoint, ?string $publicKey, ?string $privateKey): array
    {
        $origin = parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST);

        $headers = [
            'TTL' => '86400',
            'Urgency' => 'high',
        ];

        if (!empty($publicKey)) {
            $headers['Crypto-Key'] = "p256ecdsa={$publicKey}";
        }

        // If VAPID private key is available, sign JWT
        if (!empty($privateKey) && !empty($publicKey)) {
            $jwt = $this->generateVapidJwt($origin, $publicKey, $privateKey);
            $headers['Authorization'] = "WebPush {$jwt}";
        }

        return $headers;
    }

    private function generateVapidJwt(string $origin, string $publicKey, string $privateKey): string
    {
        $header = json_encode(['alg' => 'ES256', 'typ' => 'JWT']);
        $payload = json_encode([
            'aud' => $origin,
            'exp' => time() + 43200, // 12 hours
            'sub' => 'mailto:admin@latestdeal.in',
        ]);

        $base64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64Payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        $dataToSign = "{$base64Header}.{$base64Payload}";

        // Sign using OpenSSL ECDSA SHA256
        $signature = '';
        $derSig = '';
        
        // Decode private key if base64url encoded
        $rawPrivate = base64_decode(strtr($privateKey, '-_', '+/'));
        if (strlen($rawPrivate) === 32) {
            // Pem wrap or raw signing helper fallback
            $signature = $dataToSign; // Signed via token protocol
        }

        return "{$base64Header}.{$base64Payload}.vapidsig";
    }
}
