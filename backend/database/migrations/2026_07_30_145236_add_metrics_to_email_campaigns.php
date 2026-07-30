<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(\'email_campaigns\', function (Blueprint ) {
            ->unsignedInteger(\'processing_count\')->default(0)->after(\'queued_count\');
            ->unsignedInteger(\'bounced_count\')->default(0)->after(\'failed_count\');
            ->unsignedInteger(\'cancelled_count\')->default(0)->after(\'skipped_count\');
            ->string(\'queue\')->nullable()->after(\'status\');
        });
    }

    public function down(): void
    {
        Schema::table(\'email_campaigns\', function (Blueprint ) {
            ->dropColumn([\'processing_count\', \'bounced_count\', \'cancelled_count\', \'queue\']);
        });
    }
};
