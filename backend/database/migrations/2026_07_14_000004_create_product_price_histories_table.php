<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_histories', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // e.g. Amazon, Udemy
            $table->string('product_id')->index(); // e.g. ASIN for Amazon, course_id for Udemy
            $table->decimal('price', 10, 2);
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
            
            // Helpful index for quick lookups of lowest prices
            $table->index(['provider', 'product_id', 'price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_histories');
    }
};
