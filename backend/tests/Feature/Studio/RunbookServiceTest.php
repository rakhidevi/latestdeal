<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\RunbookService;

class RunbookServiceTest extends TestCase
{
    protected RunbookService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RunbookService();
    }

    public function test_it_returns_runbook_catalog()
    {
        $catalog = $this->service->getRunbookCatalog();
        
        $this->assertIsArray($catalog);
        $this->assertNotEmpty($catalog);
        
        $this->assertArrayHasKey('id', $catalog[0]);
        $this->assertArrayHasKey('title', $catalog[0]);
        $this->assertArrayHasKey('category', $catalog[0]);
    }

    public function test_it_returns_specific_runbook_details()
    {
        $runbook = $this->service->getRunbook('amazon-dom-regression');
        
        $this->assertNotNull($runbook);
        $this->assertArrayHasKey('trigger_conditions', $runbook);
        $this->assertArrayHasKey('symptoms', $runbook);
        $this->assertArrayHasKey('recovery_steps', $runbook);
        $this->assertArrayHasKey('deep_links', $runbook);
    }

    public function test_it_runs_automated_diagnostics()
    {
        $diagnostics = $this->service->runDiagnostics('amazon-dom-regression');
        
        $this->assertIsArray($diagnostics);
        $this->assertNotEmpty($diagnostics);
        $this->assertArrayHasKey('status', $diagnostics[0]);
    }
}
