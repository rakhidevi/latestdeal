<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Deal;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Deal::where(function ($query) {
            $query->whereNull('slug')->orWhereNull('hash_id');
        })->chunkById(100, function ($deals) {
            foreach ($deals as $deal) {
                if (empty($deal->slug)) {
                    $baseSlug = Str::slug($deal->title);
                    if (empty($baseSlug)) {
                        $baseSlug = 'deal';
                    }
                    $slug = $baseSlug;
                    $count = 1;
                    while (Deal::where('slug', $slug)->where('id', '!=', $deal->id)->exists()) {
                        $slug = $baseSlug . '-' . $count++;
                    }
                    $deal->slug = $slug;
                }
                if (empty($deal->hash_id)) {
                    $hash = Str::random(6);
                    while (Deal::where('hash_id', $hash)->where('id', '!=', $deal->id)->exists()) {
                        $hash = Str::random(6);
                    }
                    $deal->hash_id = $hash;
                }
                // Save without triggering events just to be safe
                $deal->saveQuietly();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to reverse for data backfill
    }
};
