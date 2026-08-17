<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
            }
            if (!Schema::hasColumn('users', 'avatar_url')) {
                $table->string('avatar_url')->nullable();
            }
            if (!Schema::hasColumn('users', 'author_slug')) {
                $table->string('author_slug')->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'social_links')) {
                $table->json('social_links')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'avatar_url', 'author_slug', 'social_links']);
        });
    }
};
