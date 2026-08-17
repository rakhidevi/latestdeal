<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->nullable()->index(); 
            $table->foreignId('deal_id')->nullable()->constrained('deals')->onDelete('cascade');
            
            // varchar instead of enum for flexibility
            $table->string('interaction_type')->index(); 
            
            // Enhanced tracking
            $table->string('source')->nullable()->index(); // dashboard, brand, category, search, recommendation, notification, homepage, editorial
            $table->string('device')->nullable();
            $table->string('platform')->nullable();
            $table->string('referrer')->nullable();
            $table->string('ip_hash')->nullable();
            $table->string('user_agent_hash')->nullable();
            
            // Full event payload for Timeline / Analytics
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'interaction_type', 'created_at'], 'idx_user_interaction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_interactions');
    }
};
