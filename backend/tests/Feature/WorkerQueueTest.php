<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ScraperJob;
use App\Models\Deal;

class WorkerQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_can_claim_pending_job()
    {
        $job = \Illuminate\Support\Facades\DB::table('scraper_jobs')->insertGetId([
            'name' => 'Test Job',
            'type' => 'ingestion',
            'status' => 'PENDING',
            'payload' => json_encode(['url' => 'https://example.com']),
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $response = $this->getJson('/api/worker/jobs/claim?worker_id=worker-test', [
            'Authorization' => 'Bearer ' . env('WORKER_API_KEY')
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['jobs' => [['job_id', 'type', 'payload']]]);
        
        $this->assertDatabaseHas('scraper_jobs', [
            'id' => $job,
            'status' => 'CLAIMED',
            'worker_id' => 'worker-test'
        ]);
    }

    public function test_self_healing_command_requeues_stale_jobs()
    {
        // Insert a job that was claimed 15 minutes ago
        $jobId = \Illuminate\Support\Facades\DB::table('scraper_jobs')->insertGetId([
            'name' => 'Stale Claimed',
            'type' => 'ingestion',
            'status' => 'CLAIMED',
            'claimed_at' => now()->subMinutes(15),
            'worker_id' => 'worker-test',
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        // Job stuck in PROCESSING for 10 minutes since last heartbeat
        $processingJob = ScraperJob::create([
            'name' => 'Stale Processing',
            'type' => 'ingestion',
            'status' => 'PROCESSING',
            'heartbeat_at' => now()->subMinutes(10),
            'worker_id' => 'worker-test'
        ]);

        // Healthy job, should not be touched
        $healthyJob = ScraperJob::create([
            'name' => 'Healthy Processing',
            'type' => 'ingestion',
            'status' => 'PROCESSING',
            'heartbeat_at' => now()->subMinutes(1),
            'worker_id' => 'worker-test'
        ]);

        $this->artisan('scraper:heal-queue')->assertSuccessful();

        $this->assertDatabaseHas('scraper_jobs', [
            'id' => $jobId,
            'status' => 'PENDING',
            'worker_id' => null
        ]);

        $this->assertDatabaseHas('scraper_jobs', [
            'id' => $processingJob->id,
            'status' => 'PENDING',
            'worker_id' => null
        ]);

        $this->assertDatabaseHas('scraper_jobs', [
            'id' => $healthyJob->id,
            'status' => 'PROCESSING',
            'worker_id' => 'worker-test'
        ]);
    }

    public function test_cancellation_updates_running_jobs()
    {
        $jobId = \Illuminate\Support\Facades\DB::table('scraper_jobs')->insertGetId([
            'name' => 'Running Job',
            'type' => 'ingestion',
            'status' => 'PROCESSING',
            'worker_id' => 'worker-test',
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        // Admin stops scraper
        $adminUser = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser)->postJson('/admin/scraper/stop'); // Assuming route exists and uses stopScraper()

        $this->assertDatabaseHas('scraper_jobs', [
            'id' => $jobId,
            'status' => 'CANCEL_REQUESTED'
        ]);
    }
}
