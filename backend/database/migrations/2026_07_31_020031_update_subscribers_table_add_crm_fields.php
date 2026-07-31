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
        Schema::table('subscribers', function (Blueprint $table) {
            if (!Schema::hasColumn('subscribers', 'first_name')) {
                $table->string('first_name')->nullable()->after('email');
            }
            if (!Schema::hasColumn('subscribers', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('subscribers', 'phone')) {
                $table->string('phone')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('subscribers', 'metadata')) {
                $table->json('metadata')->nullable()->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('subscribers', 'first_name')) $columnsToDrop[] = 'first_name';
            if (Schema::hasColumn('subscribers', 'last_name')) $columnsToDrop[] = 'last_name';
            if (Schema::hasColumn('subscribers', 'phone')) $columnsToDrop[] = 'phone';
            if (Schema::hasColumn('subscribers', 'metadata')) $columnsToDrop[] = 'metadata';
            
            if (count($columnsToDrop) > 0) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
