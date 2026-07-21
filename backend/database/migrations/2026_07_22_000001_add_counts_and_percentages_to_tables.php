<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deals') && !Schema::hasColumn('deals', 'discount_percentage')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->index()->after('discounted_price');
            });
        }

        if (Schema::hasTable('brands') && !Schema::hasColumn('brands', 'deal_count')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->integer('deal_count')->default(0)->index()->after('is_active');
            });
        }

        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'deal_count')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->integer('deal_count')->default(0)->index();
            });
        }

        if (Schema::hasTable('merchants') && !Schema::hasColumn('merchants', 'deal_count')) {
            Schema::table('merchants', function (Blueprint $table) {
                $table->integer('deal_count')->default(0)->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('deals', 'discount_percentage')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropColumn('discount_percentage');
            });
        }
        if (Schema::hasColumn('brands', 'deal_count')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropColumn('deal_count');
            });
        }
        if (Schema::hasColumn('categories', 'deal_count')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('deal_count');
            });
        }
        if (Schema::hasColumn('merchants', 'deal_count')) {
            Schema::table('merchants', function (Blueprint $table) {
                $table->dropColumn('deal_count');
            });
        }
    }
};
