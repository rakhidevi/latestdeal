<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('uic_visitors')) {
            Schema::table('uic_visitors', function (Blueprint $table) {
                if (!Schema::hasColumn('uic_visitors', 'ip_address')) {
                    $table->string('ip_address')->nullable()->after('ip_hash');
                }
            });
        }

        if (Schema::hasTable('uic_visitor_sessions')) {
            Schema::table('uic_visitor_sessions', function (Blueprint $table) {
                if (!Schema::hasColumn('uic_visitor_sessions', 'ip_address')) {
                    $table->string('ip_address')->nullable()->after('visitor_uuid');
                }
            });
        }

        if (Schema::hasTable('uic_search_history')) {
            Schema::table('uic_search_history', function (Blueprint $table) {
                if (!Schema::hasColumn('uic_search_history', 'brand_detected')) {
                    $table->string('brand_detected')->nullable();
                }
                if (!Schema::hasColumn('uic_search_history', 'product_detected')) {
                    $table->string('product_detected')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // No-op for safety
    }
};
