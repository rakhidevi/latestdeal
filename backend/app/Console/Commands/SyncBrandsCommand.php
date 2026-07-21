<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BrandSyncService;

class SyncBrandsCommand extends Command
{
    protected $signature = 'deals:sync-brands';
    protected $description = 'Synchronize brands, link deals to brand_id, and recalculate deal_count metrics for navigation.';

    public function handle(BrandSyncService $syncService): int
    {
        $this->info('Starting brand detection and count recalculation...');
        
        $result = $syncService->syncAllDeals();

        $this->info("Processed {$result['processed']} deals.");
        if ($result['unknown'] > 0) {
            $this->warn("{$result['unknown']} deals assigned to 'Unknown Brand'.");
        } else {
            $this->info('All deals matched known brands successfully.');
        }

        $this->info('Recalculated precomputed deal counts for categories, brands, and merchants.');
        return 0;
    }
}
