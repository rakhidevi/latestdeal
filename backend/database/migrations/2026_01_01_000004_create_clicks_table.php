<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('publisher_integration_id')->nullable()->index();
            
            $table->string('ip_address', 45); // Support IPv6
            $table->text('user_agent')->nullable();
            
            // Optional: If you want to track which publisher generated the click
            $table->foreignId('publisher_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->boolean('is_bot')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
