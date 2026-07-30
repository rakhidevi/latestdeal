<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(\'queue_snapshots\', function (Blueprint ) {
            ->id();
            ->string(\'queue\');
            ->integer(\'size\');
            ->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(\'queue_snapshots\');
    }
};
