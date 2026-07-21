<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add url_hash unique constraint + price_bumped_at column for deal ingestion integrity.
     * 
     * Prevents:
     *   - Duplicate deals with same URL being inserted twice
     *   - 100% off / invalid deals (enforced at app layer, migration just adds the column)
     * 
     * Adds:
     *   - url_hash: MD5 hash of the URL, used as a unique key (URLs can be too long for MySQL unique index)
     *   - price_bumped_at: Timestamp set when deal price drops (deal is surfaced to top of feed)
     */
    public function up(): void
    {
        // Step 1: Deduplicate existing URL duplicates before adding constraint
        // Keep the LOWEST id (oldest record) for each URL
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

        // Step 2: Add url_hash column and price_bumped_at
        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'url_hash')) {
                $table->string('url_hash', 32)->nullable()->after('url');
            }
            if (!Schema::hasColumn('deals', 'price_bumped_at')) {
                $table->timestamp('price_bumped_at')->nullable()->after('url_hash');
            }
        });

        // Step 3: Backfill url_hash for all existing deals
        DB::table('deals')->whereNull('url_hash')->orWhere('url_hash', '')->get(['id', 'url'])->each(function ($deal) {
            DB::table('deals')->where('id', $deal->id)->update([
                'url_hash' => md5(trim($deal->url ?? ''))
            ]);
        });

        // Step 4: Add unique index on url_hash (safe — 32 chars, no length issue)
        if (!$this->indexExists('deals', 'deals_url_hash_unique')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->unique('url_hash', 'deals_url_hash_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            if ($this->indexExists('deals', 'deals_url_hash_unique')) {
                $table->dropUnique('deals_url_hash_unique');
            }
            $table->dropColumnIfExists('url_hash');
            $table->dropColumnIfExists('price_bumped_at');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$indexName}'");
            return count($indexes) > 0;
        } catch (\Exception $e) {
            // SQLite fallback
            return false;
        }
    }
};
