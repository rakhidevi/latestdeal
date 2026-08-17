<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->string('editorial_status', 50)->default('DISCOVERED')->after('status');
            $table->text('editorial_summary')->nullable();
            $table->text('editorial_verdict')->nullable();
            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            $table->json('best_for')->nullable();
            $table->json('not_for')->nullable();
            $table->unsignedBigInteger('editor_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->boolean('is_editor_pick')->default(false);
            $table->decimal('typical_price', 10, 2)->nullable();
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
