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
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            
            $table->integer('deals_published')->default(0);
            $table->integer('total_clicks')->default(0);
            $table->decimal('estimated_revenue', 10, 2)->default(0);
            
            $table->integer('failed_jobs')->default(0);
            $table->integer('avg_queue_time')->nullable();
            
            $table->float('scraper_success_pct')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
    }
};
