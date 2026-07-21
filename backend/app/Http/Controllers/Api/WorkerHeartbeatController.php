<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkerStatus;

class WorkerHeartbeatController extends Controller
{
    /**
     * Store or update a worker's heartbeat telemetry.
     */
    public function store(Request $request)
    {
        // 1. Validate authorization
        if (env('API_KEY') && $request->header('Authorization') !== 'Bearer ' . env('API_KEY')) {
            \Illuminate\Support\Facades\Log::warning('Unauthorized worker heartbeat attempt from IP: ' . $request->ip());
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2. Validate payload schema according to future-proof contract
        $validated = $request->validate([
            'worker.id' => 'required|string|max:100',
            'worker.name' => 'required|string|max:255',
            'worker.type' => 'required|string|in:scraper,ai,queue,resolver,scheduler',
            'worker.version' => 'required|string|max:50',
            'worker.status' => 'required|string|in:online,offline,busy,paused,error,running',
            'worker.host' => 'nullable|string|max:255',
            
            'system.cpu' => 'numeric|min:0|max:100',
            'system.ram' => 'numeric|min:0',
            'system.disk' => 'numeric|min:0|max:100',
            'system.uptime' => 'integer|min:0',
            'system.started_at' => 'nullable|date',
            
            'jobs.queue' => 'integer|min:0',
            'jobs.current' => 'nullable|string',
            'jobs.success_today' => 'integer|min:0',
            'jobs.failed_today' => 'integer|min:0',
            'jobs.retry_today' => 'integer|min:0',
            'jobs.total_today' => 'integer|min:0',
            
            'health.last_success' => 'nullable|date',
            'health.last_error' => 'nullable|string',

            'timestamp' => 'required|date'
        ]);

        // Optional: Whitelist validation
        $whitelistedTypes = ['scraper', 'ai', 'queue', 'resolver', 'scheduler'];
        if (!in_array($validated['worker']['type'], $whitelistedTypes)) {
            return response()->json(['error' => 'Invalid worker type'], 400);
        }

        // 3. Update or Create Worker Status
        $worker = WorkerStatus::updateOrCreate(
            ['worker_id' => $validated['worker']['id']],
            [
                'worker_name' => $validated['worker']['name'],
                'worker_type' => $validated['worker']['type'],
                'status' => $validated['worker']['status'],
                'host_name' => $validated['worker']['host'] ?? $request->ip(),
                'host_ip' => $request->ip(),
                'version' => $validated['worker']['version'],
                'started_at' => $validated['system']['started_at'] ?? null,
                'uptime_seconds' => $validated['system']['uptime'] ?? 0,
                
                'cpu_usage' => $validated['system']['cpu'] ?? 0,
                'ram_usage' => $validated['system']['ram'] ?? 0,
                'disk_usage' => $validated['system']['disk'] ?? 0,
                
                'queue_length' => $validated['jobs']['queue'] ?? 0,
                'current_job' => $validated['jobs']['current'] ?? null,
                'jobs_today' => $validated['jobs']['total_today'] ?? 0,
                'success_today' => $validated['jobs']['success_today'] ?? 0,
                'failed_today' => $validated['jobs']['failed_today'] ?? 0,
                'retry_today' => $validated['jobs']['retry_today'] ?? 0,
                
                'last_success' => $validated['health']['last_success'] ?? null,
                'last_error' => $validated['health']['last_error'] ?? null,
                
                'last_seen' => now(), // Automatically updated by Laravel DB timestamps anyway, but explicit is better for logic
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat acknowledged',
            'worker_id' => $worker->worker_id,
            'health_status' => $worker->health_status
        ]);
    }
}
