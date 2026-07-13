<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->integer('trust_score')->nullable()->after('ai_score');
            $table->text('why_stands_out')->nullable()->after('trust_score');
            $table->json('pros')->nullable()->after('why_stands_out');
            $table->json('cons')->nullable()->after('pros');
            $table->string('best_for')->nullable()->after('cons');
            $table->string('value_rating')->nullable()->after('best_for');
            $table->decimal('lowest_price_seen', 10, 2)->nullable()->after('value_rating');
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'trust_score', 
                'why_stands_out', 
                'pros', 
                'cons', 
                'best_for', 
                'value_rating',
                'lowest_price_seen'
            ]);
        });
    }
};
