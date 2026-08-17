<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Services\Studio\AdminControlService;

class AdminControlServiceTest extends TestCase
{
    protected AdminControlService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AdminControlService();
    }

    public function test_it_returns_platform_state()
    {
        $state = $this->service->getPlatformState();
        
        $this->assertArrayHasKey('providers', $state);
        $this->assertArrayHasKey('features', $state);
        $this->assertArrayHasKey('rollout', $state);
        $this->assertArrayHasKey('governance', $state);
        $this->assertArrayHasKey('diagnostics', $state);
    }

    public function test_it_toggles_controls()
    {
        $result = $this->service->toggleControl('providers', 'amazon', false, 'user-123');
        $this->assertTrue($result);
    }

    public function test_it_updates_rollout()
    {
        $result = $this->service->updateRollout(50, 'user-123');
        $this->assertTrue($result);
    }

    public function test_it_activates_kill_switch()
    {
        $result = $this->service->activateKillSwitch('user-123');
        $this->assertTrue($result);
    }
}
