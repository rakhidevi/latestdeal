<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enrich Deals Table
        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'needs_brand_review')) {
                $table->boolean('needs_brand_review')->default(false)->index();
            }
            if (!Schema::hasColumn('deals', 'amount_saved')) {
                $table->decimal('amount_saved', 10, 2)->default(0)->index();
            }
            if (!Schema::hasColumn('deals', 'price_drop')) {
                $table->decimal('price_drop', 10, 2)->default(0)->index();
            }
            if (!Schema::hasColumn('deals', 'effective_price')) {
                $table->decimal('effective_price', 10, 2)->default(0)->index();
            }
        });

        // 2. Enrich Brands Table
        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('brands', 'country')) {
                $table->string('country')->nullable();
            }
            if (!Schema::hasColumn('brands', 'website')) {
                $table->string('website')->nullable();
            }
            if (!Schema::hasColumn('brands', 'popularity_score')) {
                $table->decimal('popularity_score', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('brands', 'ai_summary')) {
                $table->text('ai_summary')->nullable();
            }
        });

        // 3. Enrich Categories Table
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('categories', 'buying_guide')) {
                $table->text('buying_guide')->nullable();
            }
            if (!Schema::hasColumn('categories', 'faq')) {
                $table->json('faq')->nullable();
            }
            if (!Schema::hasColumn('categories', 'ai_summary')) {
                $table->text('ai_summary')->nullable();
            }
        });

        // 4. Enrich Merchants Table
        Schema::table('merchants', function (Blueprint $table) {
            if (!Schema::hasColumn('merchants', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('merchants', 'shipping_policy')) {
                $table->text('shipping_policy')->nullable();
            }
            if (!Schema::hasColumn('merchants', 'return_policy')) {
                $table->text('return_policy')->nullable();
            }
            if (!Schema::hasColumn('merchants', 'trust_score')) {
                $table->integer('trust_score')->default(80);
            }
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn(['needs_brand_review', 'amount_saved', 'price_drop', 'effective_price']);
        });
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn(['description', 'country', 'website', 'popularity_score', 'ai_summary']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'buying_guide', 'faq', 'ai_summary']);
        });
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['description', 'shipping_policy', 'return_policy', 'trust_score']);
        });
    }
};
