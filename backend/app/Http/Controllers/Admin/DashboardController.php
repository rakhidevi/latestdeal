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
        // Simple 30-day stats
        $thirtyDaysAgo = now()->subDays(30);

        $stats = [
            'total_visitors' => UicVisitor::where('created_at', '>=', $thirtyDaysAgo)->count(),
            'total_sessions' => UicVisitorSession::where('created_at', '>=', $thirtyDaysAgo)->count(),
            'total_affiliate_clicks' => UicAffiliateClick::where('created_at', '>=', $thirtyDaysAgo)->count(),
            'total_searches' => UicSearchHistory::where('created_at', '>=', $thirtyDaysAgo)->count(),
        ];

        $topSearches = UicSearchHistory::selectRaw('search_query, COUNT(*) as count')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy('search_query')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        $recentClicks = UicAffiliateClick::with('deal.merchant')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.uic_dashboard', compact('stats', 'topSearches', 'recentClicks'));
    }
}
