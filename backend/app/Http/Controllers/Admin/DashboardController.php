<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UIC\UicVisitor;
use App\Models\UIC\UicVisitorSession;
use App\Models\UIC\UicAffiliateClick;
use App\Models\UIC\UicSearchHistory;

class DashboardController extends Controller
{
    public function index()
    {
        $thirtyDaysAgo = now()->subDays(30);

        try {
            $stats = [
                'total_visitors' => class_exists('\App\Models\UIC\UicVisitor') ? UicVisitor::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
                'total_sessions' => class_exists('\App\Models\UIC\UicVisitorSession') ? UicVisitorSession::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
                'total_affiliate_clicks' => class_exists('\App\Models\UIC\UicAffiliateClick') ? UicAffiliateClick::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
                'total_searches' => class_exists('\App\Models\UIC\UicSearchHistory') ? UicSearchHistory::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
            ];
        } catch (\Exception $e) {
            $stats = ['total_visitors' => 0, 'total_sessions' => 0, 'total_affiliate_clicks' => 0, 'total_searches' => 0];
        }

        try {
            $topSearches = class_exists('\App\Models\UIC\UicSearchHistory')
                ? UicSearchHistory::selectRaw('search_term as search_query, COUNT(*) as count')
                    ->where('created_at', '>=', $thirtyDaysAgo)
                    ->groupBy('search_term')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get()
                : collect();
        } catch (\Exception $e) {
            $topSearches = collect();
        }

        try {
            $recentClicks = class_exists('\App\Models\UIC\UicAffiliateClick')
                ? UicAffiliateClick::with('deal.merchant')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get()
                : collect();
        } catch (\Exception $e) {
            $recentClicks = collect();
        }

        return view('admin.dashboard', compact('stats', 'topSearches', 'recentClicks'));
    }
}
