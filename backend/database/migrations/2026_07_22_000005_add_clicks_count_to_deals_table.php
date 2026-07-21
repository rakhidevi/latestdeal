<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add clicks_count column to deals table for analytics tracking.
     * SQLite-compatible.
     */
    public function up(): void
    {
        if (Schema::hasTable('deals')) {
            if (!Schema::hasColumn('deals', 'clicks_count')) {
                Schema::table('deals', function (Blueprint $table) {
                    $table->unsignedBigInteger('clicks_count')->default(0);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('deals')) {
            if (Schema::hasColumn('deals', 'clicks_count')) {
                Schema::table('deals', function (Blueprint $table) {
                    $table->dropColumn('clicks_count');
                });
            }
        }
    }
};
