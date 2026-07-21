<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UIC\UicVisitor;
use App\Models\UIC\UicVisitorSession;
use App\Models\UIC\UicPageVisit;
use App\Models\UIC\UicEvent;
use App\Models\UIC\UicAiConversation;
use App\Models\UIC\UicSearchHistory;
use App\Models\UIC\UicAffiliateClick;

class UicController extends Controller
{
    public function userIntelligence(Request $request)
    {
        $thirtyDaysAgo = now()->subDays(30);

        try {
            $totalVisitors = UicVisitor::count();
            $returningUsers = UicVisitor::where('return_visit_count', '>', 0)->count();
            $uniqueVisitorsToday = UicVisitor::whereDate('first_visit', today())->count();
            $liveVisitors = UicVisitorSession::where('updated_at', '>=', now()->subMinutes(15))->count();

            $visitors = UicVisitor::orderBy('last_visit', 'desc')->limit(20)->get();
            $recentSessions = UicVisitorSession::with('pageVisits')->orderBy('updated_at', 'desc')->limit(10)->get();
        } catch (\Exception $e) {
            $totalVisitors = 0; $returningUsers = 0; $uniqueVisitorsToday = 0; $liveVisitors = 0;
            $visitors = collect(); $recentSessions = collect();
        }

        return view('admin.uic.user_intelligence', compact(
            'totalVisitors', 'returningUsers', 'uniqueVisitorsToday', 'liveVisitors', 'visitors', 'recentSessions'
        ));
    }

    public function userDetail($uuid)
    {
        try {
            $visitor = UicVisitor::where('visitor_uuid', $uuid)->firstOrFail();
            $sessions = UicVisitorSession::where('visitor_uuid', $uuid)->orderBy('created_at', 'desc')->get();
            $pageVisits = UicPageVisit::where('visitor_uuid', $uuid)->orderBy('created_at', 'desc')->limit(50)->get();
            $events = UicEvent::where('visitor_uuid', $uuid)->orderBy('created_at', 'desc')->limit(50)->get();
            $aiQuestions = UicAiConversation::where('visitor_uuid', $uuid)->orderBy('created_at', 'desc')->get();
            $searches = UicSearchHistory::where('visitor_uuid', $uuid)->orderBy('created_at', 'desc')->get();
            $affiliateClicks = UicAffiliateClick::where('visitor_uuid', $uuid)->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            abort(404, 'Visitor profile not found.');
        }

        return view('admin.uic.user_detail', compact(
            'visitor', 'sessions', 'pageVisits', 'events', 'aiQuestions', 'searches', 'affiliateClicks'
        ));
    }

    public function trafficSources()
    {
        $thirtyDaysAgo = now()->subDays(30);
        try {
            $sources = UicVisitorSession::select('utm_source', 'utm_medium', 'referrer', DB::raw('count(*) as count'))
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->groupBy('utm_source', 'utm_medium', 'referrer')
                ->orderByDesc('count')
                ->limit(20)
                ->get();
        } catch (\Exception $e) {
            $sources = collect();
        }

        return view('admin.uic.traffic_sources', compact('sources'));
    }

    public function aiConversations()
    {
        try {
            $conversations = UicAiConversation::orderBy('created_at', 'desc')->paginate(20);
            $totalQuestions = UicAiConversation::count();
            $intents = UicAiConversation::select('intent', DB::raw('count(*) as count'))
                ->groupBy('intent')
                ->get();
        } catch (\Exception $e) {
            $conversations = collect(); $totalQuestions = 0; $intents = collect();
        }

        return view('admin.uic.ai_conversations', compact('conversations', 'totalQuestions', 'intents'));
    }

    public function affiliateAnalytics()
    {
        try {
            $merchantClicks = UicAffiliateClick::with('deal.merchant')
                ->select('merchant_id', DB::raw('count(*) as total_clicks'))
                ->groupBy('merchant_id')
                ->get();

            $clicks = UicAffiliateClick::with('deal.merchant')->orderBy('created_at', 'desc')->paginate(20);
        } catch (\Exception $e) {
            $merchantClicks = collect(); $clicks = collect();
        }

        return view('admin.uic.affiliate_analytics', compact('merchantClicks', 'clicks'));
    }

    public function searchAnalytics()
    {
        try {
            $topSearches = UicSearchHistory::select('search_term', DB::raw('count(*) as search_count'), DB::raw('sum(results_found) as total_results'))
                ->groupBy('search_term')
                ->orderByDesc('search_count')
                ->limit(20)
                ->get();

            $zeroResultSearches = UicSearchHistory::where('results_found', 0)
                ->select('search_term', DB::raw('count(*) as search_count'))
                ->groupBy('search_term')
                ->orderByDesc('search_count')
                ->limit(20)
                ->get();
        } catch (\Exception $e) {
            $topSearches = collect(); $zeroResultSearches = collect();
        }

        return view('admin.uic.search_analytics', compact('topSearches', 'zeroResultSearches'));
    }

    public function conversionFunnel()
    {
        try {
            $visitors = UicVisitor::count();
            $productViews = UicPageVisit::where('url', 'like', '%/deal/%')->count();
            $aiQuestions = UicAiConversation::count();
            $affiliateClicks = UicAffiliateClick::count();
        } catch (\Exception $e) {
            $visitors = 0; $productViews = 0; $aiQuestions = 0; $affiliateClicks = 0;
        }

        return view('admin.uic.conversion_funnel', compact(
            'visitors', 'productViews', 'aiQuestions', 'affiliateClicks'
        ));
    }

    public function geographicInsights()
    {
        try {
            $countries = UicVisitorSession::select('country', DB::raw('count(*) as visitor_count'))
                ->groupBy('country')
                ->orderByDesc('visitor_count')
                ->get();

            $states = UicVisitorSession::select('state', 'country', DB::raw('count(*) as visitor_count'))
                ->groupBy('state', 'country')
                ->orderByDesc('visitor_count')
                ->get();
        } catch (\Exception $e) {
            $countries = collect(); $states = collect();
        }

        return view('admin.uic.geographic_insights', compact('countries', 'states'));
    }
}
