<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerJobController extends Controller
{
    /**
     * Called by the worker to claim a batch of URLs to process.
     */
    public function claim(Request $request)
    {
        $workerId = $request->input('worker_id', 'unknown');

        // Optimistic Locking Claim Logic
        $job = null;
        
        // Find highest priority pending job
        // Priority ranking: critical -> high -> normal -> low
        $priorities = ['critical', 'high', 'normal', 'low'];
        $jobId = null;
        
        foreach ($priorities as $priority) {
            $jobId = DB::table('scraper_jobs')
                ->where('status', 'PENDING')
                ->where('priority', $priority)
                ->orderBy('created_at', 'asc')
                ->value('id');
            if ($jobId) break;
        }

        if ($jobId) {
            $updated = DB::table('scraper_jobs')
                ->where('id', $jobId)
                ->where('status', 'PENDING')
                ->update([
                    'status' => 'CLAIMED',
                    'worker_id' => $workerId,
                    'claimed_at' => now(),
                    'attempts' => DB::raw('attempts + 1')
                ]);
                
            if ($updated) {
                $job = DB::table('scraper_jobs')->find($jobId);
            }
        }

        if (!$job) {
            return response()->json(['jobs' => []]);
        }

        return response()->json([
            'jobs' => [
                [
                    'job_id' => $job->id,
                    'type' => $job->type,
                    'payload' => json_decode($job->payload, true) ?? [],
                    'attempt' => $job->attempts,
                    'created_at' => $job->created_at,
                    'priority' => $job->priority
                ]
            ]
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $status = $request->input('status');
        $workerId = $request->input('worker_id');
        
        $job = DB::table('scraper_jobs')->where('id', $id)->where('worker_id', $workerId)->first();
        if (!$job) {
            return response()->json(['error' => 'Job not found or not owned by worker'], 404);
        }

        DB::table('scraper_jobs')->where('id', $id)->update([
            'status' => $status,
            'completed_at' => in_array($status, ['COMPLETED', 'FAILED', 'CANCELLED']) ? now() : null
        ]);

        return response()->json(['success' => true]);
    }

    public function heartbeat(Request $request, $id)
    {
        $workerId = $request->input('worker_id');
        
        $updated = DB::table('scraper_jobs')
            ->where('id', $id)
            ->where('worker_id', $workerId)
            ->whereIn('status', ['CLAIMED', 'PROCESSING'])
            ->update([
                'status' => 'PROCESSING',
                'heartbeat_at' => now()
            ]);

        // If the job is cancelled, return cancelled so worker stops
        $job = DB::table('scraper_jobs')->find($id);
        $isCancelled = $job && $job->status === 'CANCEL_REQUESTED';

        return response()->json(['success' => (bool)$updated, 'cancel_requested' => $isCancelled]);
    }
}
