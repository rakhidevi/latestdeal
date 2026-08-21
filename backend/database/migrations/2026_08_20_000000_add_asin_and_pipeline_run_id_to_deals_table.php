<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->string('asin')->nullable()->after('id');
            $table->string('pipeline_run_id')->nullable()->after('trace_id');
            $table->decimal('calculated_discount_percent', 5, 2)->nullable()->after('discounted_price');
        });
        
        // Ensure ASINs are unique where they are not null. 
        Schema::table('deals', function (Blueprint $table) {
            $table->unique('asin');
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropUnique(['asin']);
            $table->dropColumn('asin');
            $table->dropColumn('pipeline_run_id');
            $table->dropColumn('calculated_discount_percent');
        });
    }
};
