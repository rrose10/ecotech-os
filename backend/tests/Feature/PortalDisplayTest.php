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
            'email' => 'p1@example.com', 'designation' => 'Designer', 'basic_salary' => 12000,
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

    public function test_dashboard_monthly_summary_counts_this_months_days(): void
    {
        [$user, $emp] = $this->member();
        $first = now()->startOfMonth();

        foreach ([['Present', 0], ['Present', 1], ['Late', 2], ['Absent', 3]] as [$status, $offset]) {
            Attendance::create([
                'employee_id' => $emp->id, 'attendance_date' => $first->copy()->addDays($offset)->toDateString(),
                'working_minutes' => 0, 'late_minutes' => 0, 'status' => $status, 'shift' => 'Night',
            ]);
        }

        $this->actingAs($user)->get('/portal/dashboard')
            ->assertOk()
            ->assertViewHas('attendanceSummary', fn ($s) => $s->present_days === 2 && $s->late_days === 1 && $s->absent_days === 1);
    }

    public function test_attendance_history_shows_break_and_hours_for_a_night_shift(): void
    {
        [$user, $emp] = $this->member();
        Attendance::create([
            'employee_id' => $emp->id, 'attendance_date' => today()->subDays(2)->toDateString(),
            'check_in' => '19:01:00', 'check_out' => '06:00:00',
            'working_minutes' => 600, 'break_minutes' => 45, 'late_minutes' => 0, 'status' => 'Present', 'shift' => 'Night',
        ]);

        $this->actingAs($user)->get('/portal/attendance')
            ->assertOk()
            ->assertSee('10h 00m')
            ->assertSee('0h 45m');
    }

    public function test_admin_overnight_checkout_counts_working_hours(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        [, $emp] = $this->member();
        $day = today()->subDays(3);

        $this->actingAs($admin)->post('/attendances', [
            'employee_id' => $emp->id,
            'attendance_date' => $day->toDateString(),
            'check_in' => $day->copy()->setTime(19, 1)->format('Y-m-d\TH:i'),
            'check_out' => $day->copy()->setTime(6, 0)->format('Y-m-d\TH:i'),
            'status' => 'Present',
        ]);

        $this->assertSame(659, (int) Attendance::first()->working_minutes);
    }

    public function test_dashboard_shows_last_month_report(): void
    {
        [$user, $emp] = $this->member();
        $prev = now()->subMonthNoOverflow()->startOfMonth();

        Attendance::create([
            'employee_id' => $emp->id, 'attendance_date' => $prev->toDateString(),
            'working_minutes' => 480, 'late_minutes' => 20, 'status' => 'Late', 'shift' => 'Night',
        ]);
        Attendance::create([
            'employee_id' => $emp->id, 'attendance_date' => $prev->copy()->addDay()->toDateString(),
            'working_minutes' => 420, 'late_minutes' => 0, 'status' => 'Present', 'shift' => 'Night',
        ]);

        $this->actingAs($user)->get('/portal/dashboard')
            ->assertOk()
            ->assertSee('Last Month Report')
            ->assertViewHas('lastMonth', fn ($m) => $m->present_days === 1 && $m->late_days === 1
                && $m->work_hours == 15.0 && $m->late_minutes === 20);
    }
}
