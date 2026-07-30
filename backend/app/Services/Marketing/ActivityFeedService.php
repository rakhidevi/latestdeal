<?php

namespace App\Services\Marketing;

use App\DTOs\Marketing\ActivityItemDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ActivityFeedService
{
    /**
     * @return ActivityItemDTO[]
     */
    public function getFeed(): array
    {
        return Cache::remember(\'marketing.activity_feed\', 5, function () {
             = DB::table(\'audits\')
                ->orderBy(\'created_at\', \'desc\')
                ->limit(20)
                ->get();
                
             = [];
            foreach ( as ) {
                 = match (->severity) {
                    \'success\' => \'text-green-500\',
                    \'warning\' => \'text-yellow-500\',
                    \'error\'   => \'text-red-500\',
                    default   => \'text-blue-500\',
                };
                
                 = match (->severity) {
                    \'success\' => \'heroicon-o-check-circle\',
                    \'warning\' => \'heroicon-o-exclamation-triangle\',
                    \'error\'   => \'heroicon-o-x-circle\',
                    default   => \'heroicon-o-information-circle\',
                };
                
                [] = new ActivityItemDTO(
                    type: \'audit\',
                    title: ->action,
                    description: ->resource ?? \'System Event\',
                    icon: ,
                    color: ,
                    timestamp: \Carbon\Carbon::parse(->created_at)->diffForHumans()
                );
            }
            
            if (empty()) {
                [] = new ActivityItemDTO(
                    type: \'system\',
                    title: \'Marketing Engine Initialized\',
                    description: \'Waiting for events...\',
                    icon: \'heroicon-o-check-circle\',
                    color: \'text-green-500\',
                    timestamp: now()->diffForHumans()
                );
            }

            return ;
        });
    }
}
