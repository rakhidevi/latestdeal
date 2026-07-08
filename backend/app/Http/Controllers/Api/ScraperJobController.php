<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScraperJob;

class ScraperJobController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:ingestion,expiry_check,metrics_sync',
        ]);

        $job = ScraperJob::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'status' => 'running',
            'logs' => [],
            'started_at' => now(),
        ]);

        return response()->json(['id' => $job->id]);
    }

    public function update(Request $request, ScraperJob $job)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:running,success,failure',
            'logs' => 'nullable|array',
        ]);

        if (isset($validated['status'])) {
            $job->status = $validated['status'];
            if ($validated['status'] !== 'running') {
                $job->completed_at = now();
                $job->duration_seconds = now()->diffInSeconds($job->started_at);
            }
        }

        if (isset($validated['logs'])) {
            // Append new logs to existing logs
            $currentLogs = $job->logs ?? [];
            $job->logs = array_merge($currentLogs, $validated['logs']);
        }

        $job->save();

        return response()->json(['success' => true]);
    }
}
