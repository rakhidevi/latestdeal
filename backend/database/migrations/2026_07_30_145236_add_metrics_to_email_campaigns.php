<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->unsignedInteger('processing_count')->default(0)->after('queued_count');
            $table->unsignedInteger('bounced_count')->default(0)->after('failed_count');
            $table->unsignedInteger('cancelled_count')->default(0)->after('skipped_count');
            $table->string('queue')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropColumn(['processing_count', 'bounced_count', 'cancelled_count', 'queue']);
        });
    }
};
