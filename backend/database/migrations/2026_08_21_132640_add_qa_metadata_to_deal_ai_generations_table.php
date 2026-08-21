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
        Schema::table('deal_ai_generations', function (Blueprint $table) {
            $table->string('content_confidence')->nullable()->after('content');
            $table->string('source_completeness')->nullable()->after('content_confidence');
            $table->json('qa_notes')->nullable()->after('qa_feedback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deal_ai_generations', function (Blueprint $table) {
            $table->dropColumn(['content_confidence', 'source_completeness', 'qa_notes']);
        });
    }
};
