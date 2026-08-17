<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'editorial_status')) {
                $table->string('editorial_status', 50)->default('DISCOVERED')->after('status');
            }
            if (!Schema::hasColumn('deals', 'editorial_summary')) {
                $table->text('editorial_summary')->nullable();
            }
            if (!Schema::hasColumn('deals', 'editorial_verdict')) {
                $table->text('editorial_verdict')->nullable();
            }
            if (!Schema::hasColumn('deals', 'pros')) {
                $table->json('pros')->nullable();
            }
            if (!Schema::hasColumn('deals', 'cons')) {
                $table->json('cons')->nullable();
            }
            if (!Schema::hasColumn('deals', 'best_for')) {
                $table->json('best_for')->nullable();
            }
            if (!Schema::hasColumn('deals', 'not_for')) {
                $table->json('not_for')->nullable();
            }
            if (!Schema::hasColumn('deals', 'editor_id')) {
                $table->unsignedBigInteger('editor_id')->nullable();
            }
            if (!Schema::hasColumn('deals', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
            if (!Schema::hasColumn('deals', 'is_editor_pick')) {
                $table->boolean('is_editor_pick')->default(false);
            }
            if (!Schema::hasColumn('deals', 'typical_price')) {
                $table->decimal('typical_price', 10, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn([
                'editorial_status',
                'editorial_summary',
                'editorial_verdict',
                'pros',
                'cons',
                'best_for',
                'not_for',
                'editor_id',
                'reviewed_at',
                'is_editor_pick',
                'typical_price'
            ]);
        });
    }
};
