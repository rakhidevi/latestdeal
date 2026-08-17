<?php

namespace Tests\Feature\Studio;

use App\Livewire\Admin\Studio\AdminCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\User;

class AdminCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user for testing
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        
        $this->guestUser = User::factory()->create([
            'role' => 'guest',
        ]);
    }

    public function test_renders_successfully()
    {
        $this->actingAs($this->adminUser);
        
        Livewire::test(AdminCenter::class)
            ->assertStatus(200)
            ->assertSee('Admin Center');
    }

    public function test_kill_switch_toggles_state()
    {
        $this->actingAs($this->adminUser);
        
        Livewire::test(AdminCenter::class)
            ->assertSet('globalKillSwitch', false) // assuming default mock state is false
            ->call('toggleGlobalKillSwitch')
            ->assertSet('globalKillSwitch', true)
            ->assertSee('Global Kill Switch is now ENABLED (All scraping halted)');
    }

    public function test_maintenance_mode_toggles_state()
    {
        $this->actingAs($this->adminUser);
        
        Livewire::test(AdminCenter::class)
            ->assertSet('maintenanceMode', false)
            ->call('toggleMaintenanceMode')
            ->assertSet('maintenanceMode', true);
    }
    
    public function test_unauthorized_users_are_blocked()
    {
        $this->actingAs($this->guestUser);
        
        $response = $this->get('/admin/studio'); // Assuming this route exists, though we're testing the component middleware
        // Alternatively, test the component directly with middleware
        
        // Since Livewire testing bypasses route middleware if not hit via HTTP, 
        // we can test the HTTP endpoint instead if it exists, but for the component itself:
        // Actually, Livewire 3 #[Middleware] attribute is executed during the component lifecycle.
        
        // This should throw an authorization exception or abort 403
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Unauthorized. You do not have permission to access the Commerce Intelligence Studio.');
        
        Livewire::test(AdminCenter::class);
    }
}
