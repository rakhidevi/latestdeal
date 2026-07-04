<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PruneDealsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prunes expired deals older than 30 days and cleans up orphaned images.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting deal prune...');

        // 1. Delete deals expired over 30 days ago
        $oldDeals = Deal::where('status', 'expired')
            ->where('updated_at', '<', now()->subDays(30))
            ->get();
            
        $count = 0;
        foreach ($oldDeals as $deal) {
            // Delete image file if exists
            if ($deal->image_path && file_exists(public_path($deal->image_path))) {
                unlink(public_path($deal->image_path));
            }
            $deal->delete();
            $count++;
        }
        
        $this->info("Pruned $count expired deals and their images.");

        // 2. Optional: Cleanup orphaned images in public/deals that don't belong to any active/expired deal
        $this->info('Scanning for orphaned images...');
        $dealImages = Deal::whereNotNull('image_path')->pluck('image_path')->toArray();
        $basePath = public_path('deals');
        
        if (File::exists($basePath)) {
            $files = File::files($basePath);
            $orphans = 0;
            foreach ($files as $file) {
                $relativePath = 'deals/' . $file->getFilename();
                if (!in_array($relativePath, $dealImages)) {
                    unlink($file->getPathname());
                    $orphans++;
                }
            }
            $this->info("Cleaned up $orphans orphaned images.");
        }

        return Command::SUCCESS;
    }
}
