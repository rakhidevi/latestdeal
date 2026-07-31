<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('worker_name')->unique();
            $table->string('queue')->nullable();
            $table->string('host')->nullable();
            $table->integer('pid')->nullable();
            $table->timestamp('last_heartbeat');
            $table->string('status')->default('running');
            $table->integer('memory_usage')->nullable();
            $table->string('laravel_version')->nullable();
            $table->string('app_version')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_heartbeats');
    }
};
