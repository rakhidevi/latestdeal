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
        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'bank_offer')) {
                $table->string('bank_offer', 255)->nullable()->after('coupon_code');
            }
            if (!Schema::hasColumn('deals', 'structured_offers')) {
                $table->json('structured_offers')->nullable()->after('bank_offer');
            }
            if (!Schema::hasColumn('deals', 'idempotency_key')) {
                $table->string('idempotency_key', 64)->nullable()->index()->after('structured_offers');
            }
            if (!Schema::hasColumn('deals', 'offer_fingerprint')) {
                $table->string('offer_fingerprint', 64)->nullable()->after('idempotency_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('deals', 'bank_offer')) {
                $columns[] = 'bank_offer';
            }
            if (Schema::hasColumn('deals', 'structured_offers')) {
                $columns[] = 'structured_offers';
            }
            if (Schema::hasColumn('deals', 'idempotency_key')) {
                $columns[] = 'idempotency_key';
            }
            if (Schema::hasColumn('deals', 'offer_fingerprint')) {
                $columns[] = 'offer_fingerprint';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
