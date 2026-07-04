<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\Merchant;
use App\Models\User;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\DB;

class AdminController
{
    public function dashboard()
    {
        $queueCount = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $metricsController = app(\App\Http\Controllers\Api\MetricsController::class);
        $metrics = $metricsController->index(request())->getData(true);

        return view('admin.dashboard', compact('queueCount', 'failedJobs', 'metrics'));
    }

    public function deals()
    {
        $deals = Deal::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.deals', compact('deals'));
    }

    public function merchants()
    {
        $merchants = Merchant::orderBy('name')->get();
        return view('admin.merchants', compact('merchants'));
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
