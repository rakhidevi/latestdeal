<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Catalog\BrandCounter;

class RecountCatalogCommand extends Command
{
    protected $signature = 'deals:recount';
    protected $description = 'Re-calculate precomputed deal_count across all categories, brands, and merchants.';

    public function handle(BrandCounter $counter): int
    {
        $this->info('Recalculating deal counts across catalog entities...');
        $res = $counter->recountAll();
        $this->info("Successfully recounted {$res['categories']} categories, {$res['brands']} brands, and {$res['merchants']} merchants.");
        return 0;
    }
}
