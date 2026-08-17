<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->json('preferred_price_range')->nullable(); // e.g. [100, 5000]
            $table->json('preferred_categories')->nullable(); // array of category slugs/ids
            $table->json('preferred_brands')->nullable(); // array of brand slugs/ids
            $table->integer('preferred_discount')->nullable(); // e.g. minimum 50
            $table->json('preferred_providers')->nullable(); // e.g. ['amazon', 'udemy']
            $table->string('preferred_language')->default('en');
            $table->json('notification_preferences')->nullable(); // e.g. {'email': true, 'browser': false}
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
