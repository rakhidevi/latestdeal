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
        Schema::create('uic_visitors', function (Blueprint $table) {
            $table->uuid('visitor_uuid')->primary();
            $table->string('ip_hash')->nullable();
            $table->timestamp('first_seen')->useCurrent();
            $table->timestamp('last_seen')->useCurrent();
            $table->integer('total_sessions')->default(0);
            $table->integer('total_pageviews')->default(0);
            $table->integer('total_ai_questions')->default(0);
            $table->integer('total_affiliate_clicks')->default(0);
            $table->integer('total_duration')->default(0);
            $table->string('favorite_category')->nullable();
            $table->string('favorite_merchant')->nullable();
            $table->timestamps();
        });

        Schema::create('uic_visitor_sessions', function (Blueprint $table) {
            $table->string('session_id')->primary();
            $table->uuid('visitor_uuid');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->default(0);
            $table->string('entry_page')->nullable();
            $table->string('exit_page')->nullable();
            $table->integer('pages_count')->default(0);
            $table->integer('events_count')->default(0);
            $table->integer('affiliate_clicks')->default(0);
            $table->integer('ai_questions')->default(0);
            $table->boolean('bounce')->default(true);
            $table->string('device')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('screen_resolution')->nullable();
            $table->string('language')->nullable();
            $table->string('timezone')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->text('referrer')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->string('bot_name')->nullable();
            $table->timestamps();
            
            // Foreign key (if desired, but often UUID strings don't necessarily need strict constraints in analytics databases)
            // $table->foreign('visitor_uuid')->references('visitor_uuid')->on('uic_visitors')->onDelete('cascade');
            $table->index('visitor_uuid');
        });

        Schema::create('uic_page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->uuid('visitor_uuid');
            $table->text('url');
            $table->text('title')->nullable();
            $table->timestamp('time_entered')->useCurrent();
            $table->timestamp('time_left')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->integer('scroll_depth')->default(0);
            $table->timestamps();
            
            $table->index('session_id');
            $table->index('visitor_uuid');
        });

        Schema::create('uic_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->uuid('visitor_uuid');
            $table->unsignedBigInteger('page_visit_id')->nullable();
            $table->string('event_type'); // e.g. CLICK, SEARCH, SHARE
            $table->string('event_name')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            
            $table->index('session_id');
            $table->index('visitor_uuid');
        });

        Schema::create('uic_ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->uuid('visitor_uuid');
            $table->text('question');
            $table->string('intent')->nullable();
            $table->string('merchant_detected')->nullable();
            $table->string('brand_detected')->nullable();
            $table->string('category_detected')->nullable();
            $table->integer('response_time')->nullable();
            $table->integer('tokens')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();
            $table->string('clicked_product')->nullable();
            $table->string('clicked_affiliate')->nullable();
            $table->timestamps();
            
            $table->index('session_id');
        });

        Schema::create('uic_search_history', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->uuid('visitor_uuid');
            $table->string('search_term');
            $table->string('normalized_search')->nullable();
            $table->integer('results_found')->default(0);
            $table->boolean('clicked')->default(false);
            $table->string('clicked_product')->nullable();
            $table->integer('clicked_position')->nullable();
            $table->integer('latency_ms')->nullable();
            $table->timestamps();
            
            $table->index('session_id');
        });

        Schema::create('uic_affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->uuid('visitor_uuid');
            $table->string('merchant');
            $table->string('product')->nullable();
            $table->unsignedBigInteger('deal_id')->nullable();
            $table->string('category')->nullable();
            $table->text('affiliate_url');
            $table->text('destination')->nullable();
            $table->string('source_page')->nullable();
            $table->string('placement')->nullable();
            $table->string('device')->nullable();
            $table->string('country')->nullable();
            $table->text('referrer')->nullable();
            $table->timestamps();
            
            $table->index('session_id');
            $table->index('deal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uic_affiliate_clicks');
        Schema::dropIfExists('uic_search_history');
        Schema::dropIfExists('uic_ai_conversations');
        Schema::dropIfExists('uic_events');
        Schema::dropIfExists('uic_page_visits');
        Schema::dropIfExists('uic_visitor_sessions');
        Schema::dropIfExists('uic_visitors');
    }
};
