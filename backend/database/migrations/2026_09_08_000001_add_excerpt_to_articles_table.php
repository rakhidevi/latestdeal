<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('articles') && !Schema::hasColumn('articles', 'excerpt')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->text('excerpt')->nullable()->after('summary');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'excerpt')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropColumn('excerpt');
            });
        }
    }
};
