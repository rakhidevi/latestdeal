<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(\'audits\', function (Blueprint ) {
            ->id();
            ->foreignId(\'user_id\')->nullable();
            ->string(\'action\');
            ->string(\'resource\')->nullable();
            ->json(\'payload\')->nullable();
            ->string(\'severity\')->default(\'info\'); // info, success, warning, error
            ->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(\'audits\');
    }
};
