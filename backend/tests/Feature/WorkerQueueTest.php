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
        $job = ScraperJob::create([
            'name' => 'Test Job',
            'type' => 'URL_SCAN',
            'status' => 'PENDING',
            'payload' => ['url' => 'https://example.com']
        ]);

        $response = $this->getJson('/api/worker/jobs/claim?worker_id=worker-test', [
            'Authorization' => 'Bearer ' . env('WORKER_API_KEY')
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['jobs' => [['job_id', 'type', 'payload']]]);
        
        $this->assertDatabaseHas('scraper_jobs', [
            'id' => $job->id,
            'status' => 'CLAIMED',
            'worker_id' => 'worker-test'
        ]);
    }

    public function test_self_healing_command_requeues_stale_jobs()
    {
        // Job stuck in CLAIMED for 10 minutes
        $claimedJob = ScraperJob::create([
            'name' => 'Stale Claimed',
            'type' => 'URL_SCAN',
            'status' => 'CLAIMED',
            'claimed_at' => now()->subMinutes(10),
            'worker_id' => 'worker-test'
        ]);

        // Job stuck in PROCESSING for 10 minutes since last heartbeat
        $processingJob = ScraperJob::create([
            'name' => 'Stale Processing',
            'type' => 'URL_SCAN',
            'status' => 'PROCESSING',
            'heartbeat_at' => now()->subMinutes(10),
            'worker_id' => 'worker-test'
        ]);

        // Healthy job, should not be touched
        $healthyJob = ScraperJob::create([
            'name' => 'Healthy Processing',
            'type' => 'URL_SCAN',
            'status' => 'PROCESSING',
            'heartbeat_at' => now()->subMinutes(1),
            'worker_id' => 'worker-test'
        ]);

        $this->artisan('scraper:heal-queue')->assertSuccessful();

        $this->assertDatabaseHas('scraper_jobs', [
            'id' => $claimedJob->id,
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
        $runningJob = ScraperJob::create([
            'name' => 'Running Job',
            'type' => 'URL_SCAN',
            'status' => 'PROCESSING',
            'worker_id' => 'worker-test'
        ]);

        // Admin stops scraper
        $this->postJson('/admin/scraper/stop'); // Assuming route exists and uses stopScraper()

        $this->assertDatabaseHas('scraper_jobs', [
            'id' => $runningJob->id,
            'status' => 'CANCEL_REQUESTED'
        ]);
    }
}
