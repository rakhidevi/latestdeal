<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing columns to uic_affiliate_clicks table for seamless analytics & click tracking.
     * SQLite-compatible.
     */
    public function up(): void
    {
        if (Schema::hasTable('uic_affiliate_clicks')) {
            Schema::table('uic_affiliate_clicks', function (Blueprint $table) {
                if (!Schema::hasColumn('uic_affiliate_clicks', 'merchant_id')) {
                    $table->unsignedBigInteger('merchant_id')->nullable();
                }
                if (!Schema::hasColumn('uic_affiliate_clicks', 'clicked_url')) {
                    $table->text('clicked_url')->nullable();
                }
                if (!Schema::hasColumn('uic_affiliate_clicks', 'ip_hash')) {
                    $table->string('ip_hash', 64)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('uic_affiliate_clicks')) {
            Schema::table('uic_affiliate_clicks', function (Blueprint $table) {
                if (Schema::hasColumn('uic_affiliate_clicks', 'merchant_id')) {
                    $table->dropColumn('merchant_id');
                }
                if (Schema::hasColumn('uic_affiliate_clicks', 'clicked_url')) {
                    $table->dropColumn('clicked_url');
                }
                if (Schema::hasColumn('uic_affiliate_clicks', 'ip_hash')) {
                    $table->dropColumn('ip_hash');
                }
            });
        }
    }
};
