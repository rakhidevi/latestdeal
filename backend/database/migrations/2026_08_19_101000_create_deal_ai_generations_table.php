<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deal_ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->onDelete('cascade');
            $table->integer('generation_number');
            $table->string('generation_target')->default('all');
            $table->string('model')->nullable();
            $table->string('provider')->nullable();
            $table->string('status')->default('pending');
            $table->json('content')->nullable();
            $table->json('source_facts')->nullable();
            $table->boolean('qa_result')->nullable();
            $table->text('qa_feedback')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            
            $table->index(['deal_id', 'generation_number']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deal_ai_generations');
    }
};
