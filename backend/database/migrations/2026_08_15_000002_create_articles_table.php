<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('articles')) {
            Schema::create('articles', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('summary')->nullable();
                $table->longText('content');
                $table->string('status', 50)->default('draft'); // draft, review, published
                $table->unsignedBigInteger('author_id');
                $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
                $table->unsignedBigInteger('category_id')->nullable();
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
                $table->string('featured_image')->nullable();
                $table->json('seo_metadata')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
