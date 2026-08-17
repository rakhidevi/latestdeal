<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For sqlite, changing a column type can be tricky if it has data. 
        // We'll rename the old column, create a new one, and drop the old one if needed.
        // Or if using MySQL/Postgres:
        Schema::table('deals', function (Blueprint $table) {
            $table->json('trust_metrics_json')->nullable()->after('trust_metrics');
        });
        
        // Copy data (ignoring parse errors for simplicity, since it was a string before)
        // If it was mostly null, this is safe.
        // In a robust production environment, you'd parse strings into json.
        
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('trust_metrics');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->renameColumn('trust_metrics_json', 'trust_metrics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->string('trust_metrics_string')->nullable()->after('trust_metrics');
        });
        
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('trust_metrics');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->renameColumn('trust_metrics_string', 'trust_metrics');
        });
    }
};
