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
        $queueCount = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $metricsController = app(\App\Http\Controllers\Api\MetricsController::class);
        $metrics = $metricsController->index(request())->getData(true);
        $pipelineSetting = Setting::where('key', 'deal_approval_pipeline')->first();
        $pipelineEnabled = $pipelineSetting ? $pipelineSetting->value === 'enabled' : false;

        // Task 2: Click Stats & Analytics
        $clickStats = DB::table('clicks')
            ->join('deals', 'clicks.deal_id', '=', 'deals.id')
            ->join('merchants', 'deals.merchant_id', '=', 'merchants.id')
            ->select('merchants.id', 'merchants.name', 'merchants.domain', DB::raw('count(*) as click_count'))
            ->groupBy('merchants.id', 'merchants.name', 'merchants.domain')
            ->orderByDesc('click_count')
            ->get();
            
        $topProducts = DB::table('clicks')
            ->join('deals', 'clicks.deal_id', '=', 'deals.id')
            ->select('deals.id', 'deals.title', 'deals.image_path', DB::raw('count(*) as click_count'))
            ->groupBy('deals.id', 'deals.title', 'deals.image_path')
            ->orderByDesc('click_count')
            ->limit(10)
            ->get();

        $categoryStats = DB::table('clicks')
            ->join('deals', 'clicks.deal_id', '=', 'deals.id')
            ->join('categories', 'deals.category_id', '=', 'categories.id')
            ->select(
                'categories.name', 
                'categories.commission_rate', 
                DB::raw('count(clicks.id) as click_count'),
                DB::raw('SUM(deals.discounted_price * (categories.commission_rate / 100) * 0.03) as estimated_revenue')
            )
            ->groupBy('categories.id', 'categories.name', 'categories.commission_rate')
            ->orderByDesc('click_count')
            ->get();

        // Task 4: Scraper Monitoring
        $scraperStats = [
            'ingested_1h' => Deal::where('created_at', '>=', now()->subHour())->count(),
            'ingested_24h' => Deal::where('created_at', '>=', now()->subDay())->count(),
            'published_24h' => Deal::where('created_at', '>=', now()->subDay())->where('status', 'active')->count(),
            'source_counts' => Deal::select('merchant_id', DB::raw('count(*) as total'))
                ->groupBy('merchant_id')
                ->with('merchant')
                ->get()
        ];

        return view('admin.dashboard', compact('queueCount', 'failedJobs', 'metrics', 'clickStats', 'categoryStats', 'topProducts', 'scraperStats', 'pipelineEnabled'));
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
        $deals = Deal::where('status', $status)->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        $counts = [
            'pending' => Deal::where('status', 'pending')->count(),
            'active' => Deal::where('status', 'active')->count(),
            'rejected' => Deal::where('status', 'rejected')->count(),
        ];

        return view('admin.deals', compact('deals', 'status', 'counts'));
    }

    public function updateDealStatus(Request $request, Deal $deal)
    {
        $request->validate(['status' => 'required|in:active,rejected,pending']);
        $deal->update(['status' => $request->status]);
        return back()->with('success', 'Deal status updated to ' . $request->status);
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
