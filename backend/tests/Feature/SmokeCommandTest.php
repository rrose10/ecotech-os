<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_smoke_command_opens_pages_without_crashing(): void
    {
        User::factory()->create(['role' => 'super_admin']);
        User::factory()->create(['role' => 'employee']);

        $this->artisan('ecotech:smoke')
            ->expectsOutputToContain('Crashes: 0')
            ->assertSuccessful();
    }

    public function test_old_employee_dashboard_redirects_to_portal(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'employee']))
            ->get('/employee/dashboard')
            ->assertRedirect(route('portal.dashboard'));
    }
}
