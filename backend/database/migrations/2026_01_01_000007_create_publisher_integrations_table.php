<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publisher_integrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index(); // Typically would constraint to users table, but using generic index for now since Auth module is next
            $table->string('platform', 50); // e.g., 'telegram'
            $table->text('bot_token')->nullable(); // encrypted
            $table->string('chat_id')->nullable();
            $table->string('affiliate_tag')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publisher_integrations');
    }
};
