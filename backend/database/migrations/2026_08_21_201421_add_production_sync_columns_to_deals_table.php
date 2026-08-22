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
            $table->enum('production_sync_status', ['PENDING', 'PUSHED', 'ERROR'])->default('PENDING')->after('editorial_status');
            $table->unsignedBigInteger('production_deal_id')->nullable()->after('production_sync_status');
            $table->timestamp('production_pushed_at')->nullable()->after('production_deal_id');
            $table->text('production_push_error')->nullable()->after('production_pushed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'production_sync_status',
                'production_deal_id',
                'production_pushed_at',
                'production_push_error'
            ]);
        });
    }
};
