<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            
            $table->string('keyword'); // e.g., "iPhone", "Samsung TV"
            $table->decimal('target_price', 10, 2); // e.g., 50000.00
            
            $table->boolean('is_fulfilled')->default(false); // Mark true once alerted
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_alerts');
    }
};
