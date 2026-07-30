<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(\'worker_heartbeats\', function (Blueprint ) {
            ->id();
            ->string(\'worker_name\')->unique();
            ->string(\'queue\')->nullable();
            ->string(\'host\')->nullable();
            ->integer(\'pid\')->nullable();
            ->timestamp(\'last_heartbeat\');
            ->string(\'status\')->default(\'running\');
            ->integer(\'memory_usage\')->nullable();
            ->string(\'laravel_version\')->nullable();
            ->string(\'app_version\')->nullable();
            ->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(\'worker_heartbeats\');
    }
};
