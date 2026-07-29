<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update/Re-create subscribers table
        if (!Schema::hasTable('subscribers')) {
            Schema::create('subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique()->nullable();
                $table->string('status')->default('active'); // active, unsubscribed, bounced
                $table->json('preferences')->nullable(); // e.g. {"price_drop": true, "flash_sale": true, "telegram": true}
                $table->timestamps();
            });
        } else {
            Schema::table('subscribers', function (Blueprint $table) {
                if (!Schema::hasColumn('subscribers', 'status')) {
                    $table->string('status')->default('active')->after('email');
                }
                if (!Schema::hasColumn('subscribers', 'preferences')) {
                    $table->json('preferences')->nullable()->after('status');
                }
            });
        }

        // 2. Multi-device tracking table
        if (!Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscriber_id')->constrained('subscribers')->onDelete('cascade');
                $table->string('device_key')->unique(); // Unique client hash or UUID
                $table->string('browser')->nullable();
                $table->string('browser_version')->nullable();
                $table->string('platform')->nullable();
                $table->string('os')->nullable();
                $table->string('locale')->nullable();
                $table->string('timezone')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Push Subscriptions (W3C Web Push)
        if (!Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscriber_id')->constrained('subscribers')->onDelete('cascade');
                $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('cascade');
                $table->text('endpoint');
                $table->text('p256dh'); // Encrypted
                $table->text('auth');   // Encrypted
                $table->timestamp('expiration_time')->nullable();
                $table->timestamp('last_success_at')->nullable();
                $table->timestamp('last_failure_at')->nullable();
                $table->integer('failure_count')->default(0);
                $table->timestamps();

                $table->index(['subscriber_id', 'device_id']);
            });
        }

        // 4. Notification Channels (Email, Telegram, WhatsApp, WebPush)
        if (!Schema::hasTable('notification_channels')) {
            Schema::create('notification_channels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subscriber_id')->constrained('subscribers')->onDelete('cascade');
                $table->string('type'); // web_push, email, telegram, whatsapp
                $table->text('destination'); // Encrypted: email address, chat_id, or phone
                $table->json('settings')->nullable();
                $table->boolean('verified')->default(true);
                $table->boolean('enabled')->default(true);
                $table->timestamps();

                $table->index(['subscriber_id', 'type']);
            });
        }

        // 5. Notification Logs (Telemetry & Trace IDs)
        if (!Schema::hasTable('notification_logs')) {
            Schema::create('notification_logs', function (Blueprint $table) {
                $table->id();
                $table->string('trace_id', 64)->index();
                $table->foreignId('subscriber_id')->nullable()->constrained('subscribers')->onDelete('set null');
                $table->string('channel')->index(); // web_push, email, telegram, whatsapp
                $table->string('notification_type')->nullable(); // price_drop, flash_sale, etc.
                $table->string('priority')->default('normal'); // low, normal, high, critical
                $table->string('status')->default('pending'); // queued, sent, failed, expired
                $table->integer('attempts')->default(0);
                $table->text('response')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_channels');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('devices');
    }
};
