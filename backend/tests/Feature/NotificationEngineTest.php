<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Subscriber;
use App\Models\Device;
use App\Models\PushSubscription;
use App\Models\Deal;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Merchant;
use App\Notifications\PriceDropNotification;
use App\Services\Notification\NotificationManager;

class NotificationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Brand::create(['id' => 1, 'name' => 'Test Brand', 'slug' => 'test-brand', 'is_active' => true]);
        Category::create(['id' => 1, 'name' => 'Test Category', 'slug' => 'test-category', 'is_active' => true]);
        Merchant::create(['id' => 1, 'name' => 'Test Merchant', 'domain' => 'test.com', 'store_id' => 'test', 'affiliate_param_key' => 'tag']);
    }

    public function test_api_subscribe_registers_device_and_push_subscription()
    {
        $payload = [
            'email' => 'testuser@latestdeal.in',
            'push_subscription' => [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-token-123',
                'keys' => [
                    'p256dh' => 'test_p256dh_key_data',
                    'auth' => 'test_auth_data'
                ]
            ],
            'device' => [
                'device_key' => 'device_hash_test_123',
                'browser' => 'Chrome',
                'platform' => 'Windows'
            ]
        ];

        $response = $this->postJson('/api/subscribe', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('subscribers', ['email' => 'testuser@latestdeal.in']);
        $this->assertDatabaseHas('devices', ['device_key' => 'device_hash_test_123']);
        $this->assertDatabaseHas('push_subscriptions', ['endpoint' => 'https://fcm.googleapis.com/fcm/send/test-token-123']);
    }

    public function test_notification_manager_dispatches_to_channels()
    {
        $subscriber = Subscriber::create(['email' => 'subscriber@latestdeal.in', 'status' => 'active']);
        $deal = Deal::create([
            'title' => 'Test Laptop Deal',
            'original_price' => 50000,
            'discounted_price' => 25000,
            'discount_percentage' => 50,
            'url' => 'https://example.com/laptop',
            'image_path' => 'dummy.jpg',
            'category_id' => 1,
            'merchant_id' => 1,
            'is_active' => true,
        ]);

        $manager = new NotificationManager();
        $result = $manager->send($subscriber, new PriceDropNotification($deal));

        $this->assertArrayHasKey('trace_id', $result);
        $this->assertEquals('price_drop', $result['type']);
    }
}
