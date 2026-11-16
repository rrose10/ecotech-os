<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalDisplayTest extends TestCase
{
    use RefreshDatabase;

    private function member(): array
    {
        $user = User::factory()->create(['role' => 'employee']);
        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        $emp = Employee::create([
            'department_id' => $dept->id, 'employee_id' => 'EMP1', 'name' => 'Person One',
            'email' => 'p1@example.com', 'designation' => 'Designer', 'salary' => 12000,
            'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true, 'user_id' => $user->id,
        ]);

        return [$user, $emp];
    }

    public function test_portal_attendance_history_shows_times_not_raw_timestamps(): void
    {
        [$user, $emp] = $this->member();
        Attendance::create([
            'employee_id' => $emp->id, 'attendance_date' => today()->subDay()->toDateString(),
            'check_in' => '19:05:22', 'check_out' => '23:10:00',
            'working_minutes' => 0, 'late_minutes' => 0, 'status' => 'Present', 'shift' => 'Night',
        ]);

        $this->actingAs($user)->get('/portal/attendance')
            ->assertOk()
            ->assertSee('07:05 PM')
            ->assertSee('11:10 PM')
            ->assertDontSee('19:05:22');
    }

    public function test_portal_salary_total_falls_back_to_basic_when_no_payroll(): void
    {
        [$user] = $this->member();

        $this->actingAs($user)->get('/portal/salary')->assertOk()->assertSee('12,000.00');
    }
}
