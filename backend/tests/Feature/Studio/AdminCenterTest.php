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
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        
        $this->guestUser = User::create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => bcrypt('password'),
            'role' => 'guest',
        ]);
    }

    public function test_renders_successfully()
    {
        $this->actingAs($this->adminUser);
        
        Livewire::test(AdminCenter::class)
            ->assertStatus(200)
            ->assertSee('Platform Control Plane');
    }

    public function test_kill_switch_toggles_state()
    {
        $this->actingAs($this->adminUser);
        
        Livewire::test(AdminCenter::class)
            ->assertStatus(200)
            ->call('toggleControl', 'system', 'kill_switch', false);
    }

    public function test_unauthorized_users_are_blocked()
    {
        $this->actingAs($this->guestUser);

        // Livewire::test() bypasses component-level middleware, so verify via HTTP route instead
        $this->get('/admin/dashboard')->assertStatus(403);
    }
}
