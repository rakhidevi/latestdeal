<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketingController extends Controller
{
    public function dashboard()
    {
        $metrics = [
            'active_campaigns' => EmailCampaign::whereIn('status', ['Queued', 'Sending'])->count(),
            'scheduled_campaigns' => EmailCampaign::where('status', 'Draft')->whereNotNull('scheduled_at')->count(),
            'sent_today' => EmailCampaign::whereDate('created_at', today())->sum('sent_count'),
            'failed_today' => EmailCampaign::whereDate('created_at', today())->sum('failed_count'),
        ];

        // Queue Health (Basic approximation using jobs table)
        $queueHealth = [
            'jobs_waiting' => DB::table('jobs')->count(),
            'jobs_failed' => DB::table('failed_jobs')->count(),
        ];

        return view('admin.marketing.dashboard', compact('metrics', 'queueHealth'));
    }

    public function campaigns(Request $request)
    {
        $campaigns = EmailCampaign::orderByDesc('created_at')->paginate(20);
        return view('admin.marketing.campaigns', compact('campaigns'));
    }

    public function settings(Request $request)
    {
        // Load generic settings grouped by category
        $settings = Setting::all()->groupBy('category');
        return view('admin.marketing.settings', compact('settings'));
    }
}
