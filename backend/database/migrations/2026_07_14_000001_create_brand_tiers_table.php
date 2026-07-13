<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. Apple
            $table->string('tier'); // e.g. Tier 1, Tier 2
            $table->decimal('multiplier', 5, 2)->default(1.0); // Weight multiplier
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_tiers');
    }
};
