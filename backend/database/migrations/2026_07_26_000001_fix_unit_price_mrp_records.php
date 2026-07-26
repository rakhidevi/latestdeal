<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to clean up erroneous unit-price MRP records (Rule 6).
     */
    public function up(): void
    {
        DB::table('deals')
            ->where(function($query) {
                $query->whereRaw('original_price > discounted_price * 10 AND discounted_price > 0')
                      ->orWhereRaw('original_price > 500000 AND discounted_price < 200000');
            })
            ->update([
                'original_price' => 0
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
