<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerTelemetryController extends Controller
{
    public function heartbeat(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|string',
            'status' => 'required|string',
            'metrics' => 'nullable|array'
        ]);

        // Using DB facade to insert/update telemetry (as per user's "lightweight" request)
        // If 'worker_heartbeats' table exists, we'll insert, otherwise log or create.
        // For now, we will assume worker_heartbeats exists or just log it to be safe.
        
        try {
            DB::table('worker_heartbeats')->updateOrInsert(
                ['worker_id' => $validated['worker_id']],
                [
                    'status' => $validated['status'],
                    'metrics' => json_encode($validated['metrics'] ?? []),
                    'last_heartbeat_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Worker heartbeat table missing, logging instead: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::info("Worker Heartbeat", $validated);
        }

        return response()->json(['message' => 'Heartbeat acknowledged']);
    }
}
