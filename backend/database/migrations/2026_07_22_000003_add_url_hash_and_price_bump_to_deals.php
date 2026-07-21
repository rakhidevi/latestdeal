<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add url_hash unique constraint + price_bumped_at column for deal ingestion integrity.
     * SQLite-compatible: no ->after(), no SHOW INDEX, no MySQL-only syntax.
     */
    public function up(): void
    {
        // Step 1: Deduplicate existing URL duplicates before adding unique constraint
        $duplicates = DB::table('deals')
            ->select(DB::raw('MIN(id) as keep_id, url'))
            ->groupBy('url')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('deals')
                ->where('url', $dup->url)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        // Step 2: Add url_hash column (SQLite-safe: no ->after())
        if (!Schema::hasColumn('deals', 'url_hash')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->string('url_hash', 32)->nullable();
            });
        }

        // Step 3: Add price_bumped_at column (SQLite-safe: no ->after())
        if (!Schema::hasColumn('deals', 'price_bumped_at')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->timestamp('price_bumped_at')->nullable();
            });
        }

        // Step 4: Backfill url_hash for all existing deals
        DB::table('deals')
            ->whereNull('url_hash')
            ->orWhere('url_hash', '')
            ->get(['id', 'url'])
            ->each(function ($deal) {
                DB::table('deals')->where('id', $deal->id)->update([
                    'url_hash' => md5(trim($deal->url ?? ''))
                ]);
            });

        // Step 5: Add unique index on url_hash (idempotent - catches duplicate index error)
        try {
            Schema::table('deals', function (Blueprint $table) {
                $table->unique('url_hash', 'deals_url_hash_unique');
            });
        } catch (\Exception $e) {
            // Index already exists - safe to ignore
        }
    }

    public function down(): void
    {
        // Drop unique index first
        try {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropUnique('deals_url_hash_unique');
            });
        } catch (\Exception $e) {
            // Index may not exist - safe to ignore
        }

        Schema::table('deals', function (Blueprint $table) {
            if (Schema::hasColumn('deals', 'url_hash')) {
                $table->dropColumn('url_hash');
            }
            if (Schema::hasColumn('deals', 'price_bumped_at')) {
                $table->dropColumn('price_bumped_at');
            }
        });
    }
};
