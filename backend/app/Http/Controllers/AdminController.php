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

        return view('admin.dashboard', compact('queueCount', 'failedJobs', 'metrics', 'clickStats', 'scraperStats', 'pipelineEnabled'));
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
            Http::timeout(5)->post('http://worker:8001/start');
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function stopScraper()
    {
        try {
            Http::timeout(5)->post('http://worker:8001/stop');
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function scraperStatus()
    {
        try {
            $response = Http::timeout(3)->get('http://worker:8001/status');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['running' => false, 'logs' => ["Worker daemon unreachable: " . $e->getMessage()]]);
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
            'platform' => 'required|in:telegram,instagram',
            'account_name' => 'required|string',
            'access_token' => 'required|string',
            'target_id' => 'required|string',
        ]);

        SocialAccount::create($validated);
        return back()->with('success', 'Account added!');
    }
}
