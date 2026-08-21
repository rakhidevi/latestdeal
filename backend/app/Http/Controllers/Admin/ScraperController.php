<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ScraperService;
use Illuminate\Http\Request;

class ScraperController extends Controller
{
    protected $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }

    public function actions()
    {
        $data = $this->scraperService->getActionsData();
        return view('admin.actions', $data);
    }

    public function runAction(Request $request)
    {
        $request->validate([
            'command' => 'required|string|in:cache:clear,config:clear,view:clear,optimize:clear,queue:flush,migrate'
        ]);

        try {
            $output = $this->scraperService->runArtisanCommand($request->command);
            return back()->with('success', "Command executed successfully: {$request->command}")->with('action_output', $output);
        } catch (\Exception $e) {
            return back()->with('error', "Failed to execute command: {$e->getMessage()}");
        }
    }

    public function startScraper()
    {
        try {
            $this->scraperService->queueStartScraper();
            return response()->json(['success' => true, 'message' => 'Scraper start job queued']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function stopScraper()
    {
        try {
            $this->scraperService->queueStopScraper();
            return response()->json(['success' => true, 'message' => 'Cancellation requested globally']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function scraperStatus()
    {
        try {
            $status = $this->scraperService->getScraperStatus();
            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['running' => false, 'logs' => ["Worker status error: " . $e->getMessage()]]);
        }
    }

    public function scrapeUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'type' => 'nullable|string|in:ingestion,sitestripe_automation'
        ]);

        try {
            $this->scraperService->queueScrapeUrl($request->url, $request->type);
            return response()->json(['success' => true, 'message' => 'URL queued for ingestion']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function customHunt(Request $request)
    {
        try {
            $this->scraperService->queueCustomHunt($request->all());
            return response()->json(['success' => true, 'message' => 'Custom hunt queued']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
