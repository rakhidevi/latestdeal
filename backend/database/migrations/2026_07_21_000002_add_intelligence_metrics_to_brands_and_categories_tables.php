<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->decimal('average_discount', 5, 2)->nullable()->after('product_count');
            $table->decimal('trending_score', 8, 2)->nullable()->after('average_discount');
            $table->decimal('average_ctr', 5, 2)->nullable()->after('trending_score');
            $table->integer('total_clicks')->default(0)->after('average_ctr');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->decimal('average_discount', 5, 2)->nullable()->after('slug');
            $table->decimal('trending_score', 8, 2)->nullable()->after('average_discount');
            $table->decimal('average_ctr', 5, 2)->nullable()->after('trending_score');
            $table->unsignedBigInteger('top_merchant_id')->nullable()->after('average_ctr');
            $table->integer('total_clicks')->default(0)->after('top_merchant_id');

            $table->foreign('top_merchant_id')->references('id')->on('merchants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn(['average_discount', 'trending_score', 'average_ctr', 'total_clicks']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['top_merchant_id']);
            $table->dropColumn(['average_discount', 'trending_score', 'average_ctr', 'top_merchant_id', 'total_clicks']);
        });
    }
};
