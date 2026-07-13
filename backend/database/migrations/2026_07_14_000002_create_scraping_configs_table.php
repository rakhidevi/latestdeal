<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraping_configs', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // e.g. Amazon, Udemy
            $table->string('target_name'); // e.g. Apple, Samsung, Programming
            $table->string('type'); // e.g. brand, category
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher is processed first
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraping_configs');
    }
};
