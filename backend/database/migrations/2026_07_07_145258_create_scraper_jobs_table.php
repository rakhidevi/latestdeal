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
        Schema::create('scraper_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Scrape Deal: Amazon"
            $table->enum('type', ['ingestion', 'expiry_check', 'metrics_sync'])->default('ingestion');
            $table->enum('status', ['running', 'success', 'failure'])->default('running');
            $table->json('logs')->nullable(); // JSON array of logs
            $table->integer('duration_seconds')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraper_jobs');
    }
};
