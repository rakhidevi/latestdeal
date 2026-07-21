<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('uic_daily_aggregates')) {
            Schema::create('uic_daily_aggregates', function (Blueprint $table) {
                $table->id();
                $table->date('date')->unique();
                $table->integer('visitors')->default(0);
                $table->integer('sessions')->default(0);
                $table->integer('pageviews')->default(0);
                $table->integer('clicks')->default(0);
                $table->integer('affiliate_clicks')->default(0);
                $table->integer('ai_questions')->default(0);
                $table->integer('searches')->default(0);
                $table->decimal('bounce_rate', 5, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('uic_daily_aggregates');
    }
};
