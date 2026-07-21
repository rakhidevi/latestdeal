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
        Schema::create('worker_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('worker_id')->unique();
            $table->string('worker_name');
            $table->string('worker_type'); // scraper, ai, queue, resolver, scheduler
            $table->string('status'); // online, offline, busy, paused, error
            $table->string('host_name');
            $table->string('host_ip')->nullable();
            $table->string('version');
            $table->timestamp('started_at')->nullable();
            $table->integer('uptime_seconds')->default(0);
            
            $table->float('cpu_usage')->default(0);
            $table->float('ram_usage')->default(0);
            $table->float('disk_usage')->default(0);
            
            $table->integer('queue_length')->default(0);
            $table->string('current_job')->nullable();
            $table->integer('jobs_today')->default(0);
            $table->integer('success_today')->default(0);
            $table->integer('failed_today')->default(0);
            $table->integer('retry_today')->default(0);
            
            $table->timestamp('last_success')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('last_seen')->useCurrent();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_statuses');
    }
};
