<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController
{
    /**
     * Fetch CTR and click data for publisher dashboards.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'total_clicks' => 0,
                'ctr' => '0%',
                'chart_labels' => [],
                'chart_data' => []
            ]);
        }
        
        $totalClicks = Click::where('user_id', $user->id)->count();
        
        // Group by day for the last 30 days
        $dailyClicks = Click::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
            
        $labels = $dailyClicks->pluck('date');
        $data = $dailyClicks->pluck('count');
        
        return response()->json([
            'total_clicks' => $totalClicks,
            'ctr' => 'N/A', // Requires impression tracking for true CTR
            'chart_labels' => $labels,
            'chart_data' => $data
        ]);
    }
}
