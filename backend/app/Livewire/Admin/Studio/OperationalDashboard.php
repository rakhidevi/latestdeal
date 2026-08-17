<?php

namespace App\Livewire\Admin\Studio;

use Livewire\Component;
use Livewire\Attributes\Middleware;
use Illuminate\Support\Facades\File;

#[Middleware('studio.admin')]
class OperationalDashboard extends Component
{
    public function render()
    {
        // For Validation Sprint 2, we read directly from the Python worker's shadow run_reports
        // In a real database, this would be an Eloquent query.
        $reportsPath = base_path('../worker/new/shadow_mode/run_reports');
        $runReports = [];
        
        if (File::exists($reportsPath)) {
            $files = File::files($reportsPath);
            // Sort by modified time descending
            usort($files, function($a, $b) {
                return $b->getMTime() - $a->getMTime();
            });
            
            foreach (array_slice($files, 0, 5) as $file) {
                $content = json_decode(File::get($file->getPathname()), true);
                if ($content && isset($content['metrics'])) {
                    $runReports[] = $content;
                }
            }
        }
        
        return view('livewire.admin.studio.operational-dashboard', [
            'runReports' => $runReports
        ])->layout('admin.layout');
    }
}
