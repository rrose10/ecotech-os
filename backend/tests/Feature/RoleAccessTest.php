<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/payrolls')->assertRedirect('/login');
    }

    public function test_employee_cannot_open_admin_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'employee']));

        $this->get('/payrolls')->assertRedirect(route('portal.dashboard'));
        $this->get('/dashboard')->assertRedirect(route('portal.dashboard'));
        $this->post('/payrolls', [])->assertForbidden();
    }

    public function test_admin_can_open_management_but_not_activity_logs(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get('/payrolls')->assertOk();
        $this->get('/activity-logs')->assertForbidden();
    }

    public function test_super_admin_can_open_activity_logs(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->get('/activity-logs')->assertOk();
    }

    public function test_hr_can_open_people_modules_but_not_finance(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hr']));

        $this->get('/employees')->assertOk();
        $this->get('/attendances')->assertOk();
        $this->get('/leaves')->assertOk();
        $this->get('/notices')->assertOk();

        $this->get('/payrolls')->assertRedirect(route('employees.index'));
        $this->get('/expenses')->assertRedirect(route('employees.index'));
        $this->get('/dashboard')->assertRedirect(route('employees.index'));
        $this->post('/payrolls', [])->assertForbidden();
    }
}
