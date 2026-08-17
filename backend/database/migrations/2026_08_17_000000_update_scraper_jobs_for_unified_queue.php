<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite, modifying columns with enums can be problematic. 
        // We will add the new columns first.
        Schema::table('scraper_jobs', function (Blueprint $table) {
            $table->json('payload')->nullable()->after('logs');
            $table->string('worker_id')->nullable()->after('payload');
            $table->timestamp('claimed_at')->nullable()->after('worker_id');
            $table->timestamp('heartbeat_at')->nullable()->after('claimed_at');
            $table->integer('attempts')->default(0)->after('heartbeat_at');
            $table->integer('max_attempts')->default(3)->after('attempts');
            $table->string('priority')->default('normal')->after('max_attempts');
        });

        // To safely change `type` and `status` from enum to string in SQLite without doctrine/dbal issues
        // we can use raw statements or just recreate the table if necessary.
        // Actually, in Laravel 11+ with SQLite, it natively supports renaming/modifying if we use string.
        // However, to be absolutely safe across all setups, we will use a raw query to update existing values, 
        // and just leave the column as is (SQLite doesn't strictly enforce ENUM constraints anyway unless check constraints are added).
        
        // Let's just manually update existing records to match the new status
        DB::table('scraper_jobs')->where('status', 'running')->update(['status' => 'PENDING']);
        DB::table('scraper_jobs')->where('status', 'success')->update(['status' => 'COMPLETED']);
        DB::table('scraper_jobs')->where('status', 'failure')->update(['status' => 'FAILED']);
    }

    public function down(): void
    {
        Schema::table('scraper_jobs', function (Blueprint $table) {
            $table->dropColumn([
                'payload',
                'worker_id',
                'claimed_at',
                'heartbeat_at',
                'attempts',
                'max_attempts',
                'priority'
            ]);
        });
    }
};
