<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Create tables using raw SQL
\Illuminate\Support\Facades\DB::statement("
    CREATE TABLE IF NOT EXISTS uic_visitors (
        visitor_uuid char(36) NOT NULL PRIMARY KEY,
        ip_hash varchar(255) DEFAULT NULL,
        first_seen timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        last_seen timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        total_sessions int NOT NULL DEFAULT 0,
        total_pageviews int NOT NULL DEFAULT 0,
        total_ai_questions int NOT NULL DEFAULT 0,
        total_affiliate_clicks int NOT NULL DEFAULT 0,
        total_duration int NOT NULL DEFAULT 0,
        favorite_category varchar(255) DEFAULT NULL,
        favorite_merchant varchar(255) DEFAULT NULL,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL
    )
");

\Illuminate\Support\Facades\DB::statement("
    CREATE TABLE IF NOT EXISTS uic_visitor_sessions (
        session_id varchar(255) NOT NULL PRIMARY KEY,
        visitor_uuid char(36) NOT NULL,
        started_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        ended_at timestamp NULL DEFAULT NULL,
        duration int NOT NULL DEFAULT 0,
        entry_page varchar(255) DEFAULT NULL,
        exit_page varchar(255) DEFAULT NULL,
        pages_count int NOT NULL DEFAULT 0,
        events_count int NOT NULL DEFAULT 0,
        affiliate_clicks int NOT NULL DEFAULT 0,
        ai_questions int NOT NULL DEFAULT 0,
        bounce tinyint(1) NOT NULL DEFAULT 1,
        device varchar(255) DEFAULT NULL,
        browser varchar(255) DEFAULT NULL,
        os varchar(255) DEFAULT NULL,
        screen_resolution varchar(255) DEFAULT NULL,
        language varchar(255) DEFAULT NULL,
        timezone varchar(255) DEFAULT NULL,
        country varchar(255) DEFAULT NULL,
        state varchar(255) DEFAULT NULL,
        city varchar(255) DEFAULT NULL,
        utm_source varchar(255) DEFAULT NULL,
        utm_medium varchar(255) DEFAULT NULL,
        utm_campaign varchar(255) DEFAULT NULL,
        referrer text DEFAULT NULL,
        is_bot tinyint(1) NOT NULL DEFAULT 0,
        bot_name varchar(255) DEFAULT NULL,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        INDEX (visitor_uuid)
    )
");

\Illuminate\Support\Facades\DB::statement("
    CREATE TABLE IF NOT EXISTS uic_page_visits (
        id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        session_id varchar(255) NOT NULL,
        visitor_uuid char(36) NOT NULL,
        url text NOT NULL,
        title text DEFAULT NULL,
        time_entered timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        time_left timestamp NULL DEFAULT NULL,
        duration_seconds int NOT NULL DEFAULT 0,
        scroll_depth int NOT NULL DEFAULT 0,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        INDEX (session_id),
        INDEX (visitor_uuid)
    )
");

\Illuminate\Support\Facades\DB::statement("
    CREATE TABLE IF NOT EXISTS uic_events (
        id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        session_id varchar(255) NOT NULL,
        visitor_uuid char(36) NOT NULL,
        page_visit_id bigint unsigned DEFAULT NULL,
        event_type varchar(255) NOT NULL,
        event_name varchar(255) DEFAULT NULL,
        metadata json DEFAULT NULL,
        occurred_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        INDEX (session_id),
        INDEX (visitor_uuid)
    )
");

\Illuminate\Support\Facades\DB::statement("
    CREATE TABLE IF NOT EXISTS uic_ai_conversations (
        id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        session_id varchar(255) NOT NULL,
        visitor_uuid char(36) NOT NULL,
        question text NOT NULL,
        intent varchar(255) DEFAULT NULL,
        merchant_detected varchar(255) DEFAULT NULL,
        brand_detected varchar(255) DEFAULT NULL,
        category_detected varchar(255) DEFAULT NULL,
        response_time int DEFAULT NULL,
        tokens int DEFAULT NULL,
        confidence decimal(5,2) DEFAULT NULL,
        clicked_product varchar(255) DEFAULT NULL,
        clicked_affiliate varchar(255) DEFAULT NULL,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        INDEX (session_id)
    )
");

\Illuminate\Support\Facades\DB::statement("
    CREATE TABLE IF NOT EXISTS uic_search_history (
        id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        session_id varchar(255) NOT NULL,
        visitor_uuid char(36) NOT NULL,
        search_term varchar(255) NOT NULL,
        normalized_search varchar(255) DEFAULT NULL,
        results_found int NOT NULL DEFAULT 0,
        clicked tinyint(1) NOT NULL DEFAULT 0,
        clicked_product varchar(255) DEFAULT NULL,
        clicked_position int DEFAULT NULL,
        latency_ms int DEFAULT NULL,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        INDEX (session_id)
    )
");

\Illuminate\Support\Facades\DB::statement("
    CREATE TABLE IF NOT EXISTS uic_affiliate_clicks (
        id bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        session_id varchar(255) NOT NULL,
        visitor_uuid char(36) NOT NULL,
        merchant varchar(255) NOT NULL,
        product varchar(255) DEFAULT NULL,
        deal_id bigint unsigned DEFAULT NULL,
        category varchar(255) DEFAULT NULL,
        affiliate_url text NOT NULL,
        destination text DEFAULT NULL,
        source_page varchar(255) DEFAULT NULL,
        placement varchar(255) DEFAULT NULL,
        device varchar(255) DEFAULT NULL,
        country varchar(255) DEFAULT NULL,
        referrer text DEFAULT NULL,
        created_at timestamp NULL DEFAULT NULL,
        updated_at timestamp NULL DEFAULT NULL,
        INDEX (session_id),
        INDEX (deal_id)
    )
");

$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES LIKE "uic_%"');

echo json_encode(['status' => 'success', 'tables' => $tables]);
