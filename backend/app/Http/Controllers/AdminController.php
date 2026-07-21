<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\Merchant;
use App\Models\User;
use App\Models\SocialAccount;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AdminController
{
    public function dashboard()
    {
        // Row 1: Platform Health
        $workerStatuses = \App\Models\WorkerStatus::orderBy('last_seen', 'desc')->get();
        $workersOnline = $workerStatuses->filter(fn($w) => $w->health_status === 'online')->count();
        $totalWorkers = $workerStatuses->count();
        
        $queueCount = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        $diskPath = storage_path();
        $diskTotal = @disk_total_space($diskPath) ?: 1;
        $diskFree = @disk_free_space($diskPath) ?: 0;
        $storageUsedPct = round((($diskTotal - $diskFree) / $diskTotal) * 100);

        // Dashboard Alerts
        $alerts = [];
        $offlineWorkers = $workerStatuses->filter(fn($w) => $w->health_status === 'offline');
        foreach ($offlineWorkers as $worker) {
            $alerts[] = ['type' => 'error', 'message' => "{$worker->worker_name} offline", 'icon' => 'alert-circle'];
        }
        if ($queueCount > 50) {
            $alerts[] = ['type' => 'warning', 'message' => "Queue growing ({$queueCount})", 'icon' => 'list-plus'];
        }
        if ($failedJobs > 0) {
            $alerts[] = ['type' => 'warning', 'message' => "{$failedJobs} failed jobs", 'icon' => 'alert-triangle'];
        }
        if (empty($alerts)) {
            $alerts[] = ['type' => 'success', 'message' => "All systems operational", 'icon' => 'check-circle'];
        }

        // Row 2: Business Overview
        $dealsToday = Deal::whereDate('created_at', today())->count();
        $publishedToday = Deal::whereDate('created_at', today())->where('status', 'active')->count();
        $pendingReview = Deal::where('status', 'pending')->count();
        $totalClicks = DB::table('clicks')->count();

        // Row 3: Money & Analytics
        $metricsController = app(\App\Http\Controllers\Api\MetricsController::class);
        $metrics = $metricsController->index(request())->getData(true);
        
        $clickStats = DB::table('clicks')
            ->join('deals', 'clicks.deal_id', '=', 'deals.id')
            ->join('merchants', 'deals.merchant_id', '=', 'merchants.id')
            ->select('merchants.name', DB::raw('count(*) as click_count'))
            ->groupBy('merchants.name')
            ->pluck('click_count', 'name')
            ->toArray();

        $categoryStats = DB::table('clicks')
            ->join('deals', 'clicks.deal_id', '=', 'deals.id')
            ->join('categories', 'deals.category_id', '=', 'categories.id')
            ->select(
                DB::raw('SUM(deals.discounted_price * (categories.commission_rate / 100) * 0.03) as estimated_revenue')
            )->first();
        $estimatedEarnings = $categoryStats->estimated_revenue ?? 0;
        
        $ctr = $totalClicks > 0 && Deal::count() > 0 ? round(($totalClicks / Deal::count()) * 100, 2) : 0;

        // Row 4: Quality
        $rejectedDeals = Deal::where('status', 'rejected')->count();
        $missingImages = Deal::whereNull('image_path')->orWhere('image_path', '')->count();

        // Row 5: Merchant Cards (Scraper Statistics)
        $todayDateStr = now()->toDateString();
        $merchantStats = DB::table('deals')
            ->join('merchants', 'deals.merchant_id', '=', 'merchants.id')
            ->select(
                'merchants.name',
                DB::raw('count(*) as total_deals'),
                DB::raw("SUM(CASE WHEN DATE(deals.created_at) = '{$todayDateStr}' THEN 1 ELSE 0 END) as today_deals")
            )
            ->groupBy('merchants.id', 'merchants.name')
            ->get();
            
        foreach ($merchantStats as $stat) {
            $worker = $workerStatuses->first(function($w) use ($stat) {
                return $w->worker_type === 'scraper' && stripos($w->worker_name, $stat->name) !== false;
            });
            if ($worker) {
                $stat->success_pct = ($worker->success_today + $worker->failed_today) > 0 
                    ? round(($worker->success_today / ($worker->success_today + $worker->failed_today)) * 100) 
                    : 100;
                $stat->retries = $worker->retry_today;
                $stat->last_run = $worker->last_seen ? $worker->last_seen->diffForHumans() : 'N/A';
            } else {
                $stat->success_pct = null;
                $stat->retries = null;
                $stat->last_run = null;
            }
        }

        // Row 6: Activity Feed
        $feed = collect();
        foreach(Deal::with('merchant')->orderBy('created_at', 'desc')->limit(5)->get() as $d) {
            $feed->push((object)['type' => 'deal', 'title' => 'New Deal: ' . $d->title, 'subtitle' => $d->merchant->name ?? 'Unknown', 'time' => $d->created_at, 'bg' => 'bg-emerald-500']);
        }
        foreach(DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(5)->get() as $e) {
            $feed->push((object)['type' => 'error', 'title' => 'Job Failed: ' . $e->queue, 'subtitle' => \Illuminate\Support\Str::limit($e->exception, 50), 'time' => \Carbon\Carbon::parse($e->failed_at), 'bg' => 'bg-red-500']);
        }
        foreach(DB::table('clicks')->orderBy('created_at', 'desc')->limit(5)->get() as $c) {
            $feed->push((object)['type' => 'click', 'title' => 'New Click', 'subtitle' => 'Deal ID: ' . $c->deal_id, 'time' => \Carbon\Carbon::parse($c->created_at), 'bg' => 'bg-indigo-500']);
        }
        foreach(\App\Models\WorkerStatus::orderBy('last_seen', 'desc')->limit(5)->get() as $w) {
            $feed->push((object)['type' => 'heartbeat', 'title' => 'Worker Check-in', 'subtitle' => $w->worker_name, 'time' => $w->last_seen, 'bg' => 'bg-blue-500']);
        }
        $activityFeed = $feed->sortByDesc('time')->take(8)->values();

        // UIC Analytics Stats & Fallbacks for View
        $thirtyDaysAgo = now()->subDays(30);
        
        try {
            $stats = [
                'total_visitors' => class_exists('\App\Models\UIC\UicVisitor') ? \App\Models\UIC\UicVisitor::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
                'total_sessions' => class_exists('\App\Models\UIC\UicVisitorSession') ? \App\Models\UIC\UicVisitorSession::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
                'total_affiliate_clicks' => class_exists('\App\Models\UIC\UicAffiliateClick') ? \App\Models\UIC\UicAffiliateClick::where('created_at', '>=', $thirtyDaysAgo)->count() : $totalClicks,
                'total_searches' => class_exists('\App\Models\UIC\UicSearchHistory') ? \App\Models\UIC\UicSearchHistory::where('created_at', '>=', $thirtyDaysAgo)->count() : 0,
            ];
        } catch (\Exception $e) {
            $stats = ['total_visitors' => 0, 'total_sessions' => 0, 'total_affiliate_clicks' => $totalClicks, 'total_searches' => 0];
        }

        try {
            $topSearches = class_exists('\App\Models\UIC\UicSearchHistory') 
                ? \App\Models\UIC\UicSearchHistory::selectRaw('search_term as search_query, COUNT(*) as count')
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
                ? \App\Models\UIC\UicAffiliateClick::with('deal.merchant')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get() 
                : collect();
        } catch (\Exception $e) {
            $recentClicks = collect();
        }

        // Pipeline Setting
        $pipelineSetting = Setting::where('key', 'deal_approval_pipeline')->first();
        $pipelineEnabled = $pipelineSetting ? $pipelineSetting->value === 'enabled' : false;

        return view('admin.dashboard', compact(
            'stats', 'topSearches', 'recentClicks', 'metrics',
            'workerStatuses', 'workersOnline', 'totalWorkers', 'queueCount', 'failedJobs', 'storageUsedPct', 'alerts',
            'dealsToday', 'publishedToday', 'pendingReview', 'totalClicks',
            'clickStats', 'estimatedEarnings', 'ctr',
            'rejectedDeals', 'missingImages',
            'merchantStats',
            'activityFeed',
            'pipelineEnabled'
        ));
    }

    public function actions()
    {
        $jobs = \App\Models\ScraperJob::orderBy('created_at', 'desc')->paginate(20);
        
        $metrics = [
            'total_scraped' => \App\Models\ScraperJob::where('type', 'ingestion')->count(),
            'accepted' => \App\Models\Deal::where('status', 'active')->count(),
            'rejected' => \App\Models\Deal::where('status', 'rejected')->count(),
            'expired' => \App\Models\ScraperJob::where('type', 'expiry_check')->where('status', 'success')->count() // Rough metric for expiry checks
        ];

        return view('admin.actions', compact('jobs', 'metrics'));
    }

    public function runAction(Request $request)
    {
        $request->validate([
            'command' => 'required|string|in:cache:clear,config:clear,view:clear,optimize:clear,queue:flush,migrate'
        ]);

        try {
            if ($request->command === 'migrate') {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            } else {
                \Illuminate\Support\Facades\Artisan::call($request->command);
            }
            
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            return back()->with('success', "Command executed successfully: {$request->command}")->with('action_output', $output);
        } catch (\Exception $e) {
            return back()->with('error', "Failed to execute command: {$e->getMessage()}");
        }
    }

    public function toggleSetting(Request $request)
    {
        $request->validate(['key' => 'required|string', 'value' => 'required|string']);
        Setting::updateOrCreate(
            ['key' => $request->key],
            ['value' => $request->value]
        );
        return back()->with('success', 'Setting updated successfully!');
    }

    public function startScraper()
    {
        try {
            $workerIp = gethostbyname('worker');
            Http::timeout(5)->post("http://{$workerIp}:8001/start");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function stopScraper()
    {
        try {
            $workerIp = gethostbyname('worker');
            Http::timeout(5)->post("http://{$workerIp}:8001/stop");
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function scraperStatus()
    {
        try {
            $workerIp = gethostbyname('worker');
            $response = Http::timeout(3)->get("http://{$workerIp}:8001/status");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['running' => false, 'logs' => ["Worker daemon unreachable: " . $e->getMessage()]]);
        }
    }

    public function scrapeUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'type' => 'nullable|string|in:ingestion,sitestripe_automation'
        ]);
        try {
            $workerIp = gethostbyname('worker');
            $payload = ['url' => $request->url, 'type' => $request->type ?? 'ingestion'];
            $response = Http::timeout(5)->post("http://{$workerIp}:8001/scrape", $payload);
            return response()->json(['success' => true, 'message' => $response->json('status')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function customHunt(Request $request)
    {
        try {
            $workerIp = gethostbyname('worker');
            $payload = [
                'category' => $request->category,
                'brand' => $request->brand,
                'discount' => $request->discount,
                'keyword' => $request->keyword,
                'mode' => $request->mode
            ];
            $response = Http::timeout(5)->post("http://{$workerIp}:8001/hunt", $payload);
            return response()->json(['success' => true, 'message' => $response->json('status')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deals(Request $request)
    {
        $status = $request->get('status', 'pending');
        $search = $request->get('search', '');
        
        $query = Deal::where('status', $status);
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('url', 'like', '%' . $search . '%');
            });
        }
        
        $deals = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        $counts = [
            'pending' => Deal::where('status', 'pending')->count(),
            'active' => Deal::where('status', 'active')->count(),
            'rejected' => Deal::where('status', 'rejected')->count(),
        ];

        // Count how many illegal deals currently exist across all statuses
        $illegalCount = $this->countIllegalDeals();

        // Get unique domains across all deals
        $allUrls = Deal::pluck('url')->toArray();
        $uniqueDomains = collect($allUrls)->map(function ($url) {
            return parse_url($url, PHP_URL_HOST);
        })->filter()->unique()->values();

        return view('admin.deals', compact('deals', 'status', 'counts', 'search', 'illegalCount', 'uniqueDomains'));
    }

    /**
     * Returns the count of deals matching blocked keywords.
     */
    private function countIllegalDeals(): int
    {
        $blockedKeywords = [
            'mod apk', 'modded apk', 'cracked apk',
            'premium unlocked', 'unlocked all', 'pro unlocked',
            'no watermark', 'ad free mod', 'ads removed mod',
            'crack', 'cracked', 'keygen', 'serial key',
            'pirated', 'warez', 'nulled',
            'paid apk free', 'patched apk',
        ];

        $query = Deal::query();
        $query->where(function ($q) use ($blockedKeywords) {
            foreach ($blockedKeywords as $keyword) {
                $q->orWhere('title', 'like', '%' . $keyword . '%');
            }
        });

        return $query->count();
    }

    /**
     * Permanently deletes all deals matching blocked (illegal/pirated) keywords.
     */
    public function purgeIllegalDeals()
    {
        $blockedKeywords = [
            'mod apk', 'modded apk', 'cracked apk',
            'premium unlocked', 'unlocked all', 'pro unlocked',
            'no watermark', 'ad free mod', 'ads removed mod',
            'crack', 'cracked', 'keygen', 'serial key',
            'pirated', 'warez', 'nulled',
            'paid apk free', 'patched apk',
        ];

        $query = Deal::query();
        $query->where(function ($q) use ($blockedKeywords) {
            foreach ($blockedKeywords as $keyword) {
                $q->orWhere('title', 'like', '%' . $keyword . '%');
            }
        });

        $count = $query->count();
        $query->delete();

        return back()->with('success', "Purged {$count} illegal/pirated deals.");
    }

    public function updateDealStatus(Request $request, Deal $deal)
    {
        $request->validate(['status' => 'required|in:active,rejected,pending']);
        $deal->update(['status' => $request->status]);
        return back()->with('success', 'Deal status updated to ' . $request->status);
    }

    public function destroyDeal(Deal $deal)
    {
        $deal->delete();
        return back()->with('success', 'Deal permanently deleted.');
    }

    public function merchants()
    {
        $merchants = Merchant::orderBy('name')->get();
        return view('admin.merchants', compact('merchants'));
    }

    public function storeMerchant(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'domain' => 'required|string',
            'store_id' => 'required|string',
            'affiliate_param_key' => 'required|string',
            'status' => 'boolean'
        ]);

        $validated['status'] = $request->has('status');

        Merchant::create($validated);
        return back()->with('success', 'Merchant created successfully!');
    }

    public function updateMerchant(Request $request, Merchant $merchant)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'domain' => 'required|string',
            'store_id' => 'required|string',
            'affiliate_param_key' => 'required|string',
            'status' => 'boolean'
        ]);

        $validated['status'] = $request->has('status');

        $merchant->update($validated);
        return back()->with('success', 'Merchant updated successfully!');
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function links()
    {
        $merchants = Merchant::all();
        return view('admin.links', compact('merchants'));
    }

    public function generateLink(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'merchant_id' => 'nullable|exists:merchants,id',
            'sub_id' => 'nullable|string'
        ]);

        $url = $request->url;
        $merchant = null;

        if ($request->merchant_id) {
            $merchant = Merchant::find($request->merchant_id);
        }

        if (!$merchant) {
            return response()->json(['error' => 'Please select a merchant explicitly.'], 400);
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        $trackedUrl = $url . $separator . $merchant->affiliate_param_key . '=' . $merchant->store_id;

        if ($request->sub_id) {
            $trackedUrl .= '&sub1=' . urlencode($request->sub_id);
        }

        return response()->json(['url' => $trackedUrl]);
    }

    public function workQueue()
    {
        \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]);
        return back()->with('success', 'Queue worker executed successfully.');
    }

    public function clearFailedJobs()
    {
        \Illuminate\Support\Facades\Artisan::call('queue:flush');
        return back()->with('success', 'Failed jobs cleared successfully.');
    }

    public function socialAccounts()
    {
        $accounts = SocialAccount::all();
        return view('admin.social-accounts', compact('accounts'));
    }

    public function storeSocialAccount(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:telegram,instagram,facebook,twitter',
            'account_name' => 'required|string',
            'access_token' => 'required|string',
            'target_id' => 'required|string',
        ]);

        $validated['is_active'] = true;
        $validated['user_id'] = auth()->id();

        SocialAccount::create($validated);
        return back()->with('success', 'Social account added successfully.');
    }

    public function deleteSocialAccount(SocialAccount $socialAccount)
    {
        $socialAccount->delete();
        return back()->with('success', 'Social account deleted successfully.');
    }

    public function toggleSocialAccount(SocialAccount $socialAccount)
    {
        $socialAccount->update(['is_active' => !$socialAccount->is_active]);
        return back()->with('success', 'Social account status updated.');
    }

    public function settings()
    {
        $settings = Setting::whereIn('key', ['ollama_model', 'ollama_base_url', 'ai_auto_summarize', 'crawler_automated', 'crawler_manual'])->pluck('value', 'key');
        return view('admin.settings', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'ollama_model' => 'nullable|string',
            'ollama_base_url' => 'nullable|url',
            'ai_auto_summarize' => 'nullable|string|in:enabled,disabled',
            'crawler_automated' => 'nullable|string|in:enabled,disabled',
            'crawler_manual' => 'nullable|string|in:enabled,disabled'
        ]);

        foreach (['ollama_model', 'ollama_base_url', 'ai_auto_summarize', 'crawler_automated', 'crawler_manual'] as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->input($key)]
                );
            }
        }

        return back()->with('success', 'AI Settings updated successfully.');
    }
}
