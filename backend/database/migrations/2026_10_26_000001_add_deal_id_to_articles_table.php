<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('articles') && !Schema::hasColumn('articles', 'deal_id')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->foreignId('deal_id')->nullable()->after('id')->constrained('deals')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'deal_id')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropForeign(['deal_id']);
                $table->dropColumn('deal_id');
            });
        }
    }
};
