<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Catalog\CatalogIntegrityService;

class CheckCatalogIntegrityCommand extends Command
{
    protected $signature = 'deals:check-integrity';
    protected $description = 'Perform catalog integrity health audit and report missing brands, broken references, or duplicate entities.';

    public function handle(CatalogIntegrityService $integrityService): int
    {
        $this->info('Running Catalog Integrity Health Audit...');
        $report = $integrityService->getHealthReport();

        $this->table(['Metric', 'Value'], [
            ['Total Deals', $report['deals']['total']],
            ['Active Deals', $report['deals']['active']],
            ['Deals Needing Brand Review', $report['deals']['missing_brand_review']],
            ['Deals Missing Category', $report['deals']['missing_category']],
            ['Total Brands', $report['catalog']['total_brands']],
            ['Active Brands', $report['catalog']['active_brands']],
            ['Duplicate Brand Slugs', $report['catalog']['duplicate_brands']],
            ['Navigation Cache Version', $report['navigation_cache']['current_version']],
            ['Navigation Cache Key', $report['navigation_cache']['cache_key']],
        ]);

        return 0;
    }
}
