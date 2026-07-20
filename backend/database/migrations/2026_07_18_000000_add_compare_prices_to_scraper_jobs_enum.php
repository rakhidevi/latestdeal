<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE scraper_jobs MODIFY COLUMN type ENUM('ingestion', 'expiry_check', 'metrics_sync', 'compare_prices') DEFAULT 'ingestion'");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE scraper_jobs MODIFY COLUMN type ENUM('ingestion', 'expiry_check', 'metrics_sync') DEFAULT 'ingestion'");
    }
};
