<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\User;
use App\Support\OfficeNetwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeNetworkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::flush();
    }

    private function teamUser(): User
    {
        $user = User::factory()->create(['role' => 'employee']);
        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        Employee::create([
            'department_id' => $dept->id, 'employee_id' => 'EMP1', 'name' => 'Person One',
            'email' => 'p1@example.com', 'designation' => 'Designer', 'basic_salary' => 1000,
            'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true, 'user_id' => $user->id,
        ]);

        return $user;
    }

    public function test_helper_matches_addresses_and_ranges(): void
    {
        $this->assertTrue(OfficeNetwork::allowed('10.0.0.5', ''));
        $this->assertTrue(OfficeNetwork::allowed('192.168.10.77', "192.168.10.0/24\n192.168.0.0/24"));
        $this->assertTrue(OfficeNetwork::allowed('192.168.0.9', "192.168.10.0/24, 192.168.0.0/24"));
        $this->assertTrue(OfficeNetwork::allowed('203.0.113.5', '203.0.113.5'));
        $this->assertFalse(OfficeNetwork::allowed('8.8.8.8', '192.168.10.0/24'));
        $this->assertFalse(OfficeNetwork::allowed(null, '192.168.10.0/24'));

        $this->assertTrue(OfficeNetwork::validEntry('192.168.10.0/24'));
        $this->assertTrue(OfficeNetwork::validEntry('10.1.1.1'));
        $this->assertFalse(OfficeNetwork::validEntry('192.168.10.0/40'));
        $this->assertFalse(OfficeNetwork::validEntry('not-an-ip'));
    }

    public function test_check_in_is_blocked_outside_the_office_network(): void
    {
        Setting::put('office_ips', '192.168.10.0/24');

        $this->actingAs($this->teamUser())
            ->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->post('/portal/attendance/check-in')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_check_in_works_from_the_office_network(): void
    {
        Setting::put('office_ips', '192.168.10.0/24');

        $this->actingAs($this->teamUser())
            ->withServerVariables(['REMOTE_ADDR' => '192.168.10.55'])
            ->post('/portal/attendance/check-in')
            ->assertSessionMissing('error');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_no_rule_means_check_in_works_from_anywhere(): void
    {
        $this->actingAs($this->teamUser())
            ->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->post('/portal/attendance/check-in');

        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_settings_validate_and_save_the_ip_list(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->put('/settings', ['company_name' => 'EcoTech', 'office_ips' => 'garbage'])
            ->assertSessionHasErrors('office_ips');

        $this->put('/settings', ['company_name' => 'EcoTech', 'office_ips' => "192.168.10.0/24, 192.168.0.0/24"])
            ->assertSessionHasNoErrors();

        $this->assertSame("192.168.10.0/24\n192.168.0.0/24", Setting::get('office_ips'));
        $this->get('/settings')->assertOk()->assertSee('Office IP addresses');
    }
}
