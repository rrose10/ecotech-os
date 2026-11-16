<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LateGraceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function checkInAt(string $time): Attendance
    {
        Carbon::setTestNow(Carbon::parse($time));

        $user = User::factory()->create(['role' => 'employee']);
        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        Employee::create([
            'department_id' => $dept->id, 'employee_id' => 'EMP1', 'name' => 'Person One',
            'email' => 'p1@example.com', 'designation' => 'Designer', 'salary' => 1000,
            'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true, 'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post('/portal/attendance/check-in');

        return Attendance::firstOrFail();
    }

    public function test_up_to_ten_minutes_after_shift_start_is_not_late(): void
    {
        $a = $this->checkInAt('2026-11-10 19:10:00');

        $this->assertSame(0, (int) $a->late_minutes);
        $this->assertSame('Present', $a->status);
    }

    public function test_after_ten_minutes_the_full_delay_counts_as_late(): void
    {
        $a = $this->checkInAt('2026-11-10 19:11:00');

        $this->assertSame(11, (int) $a->late_minutes);
        $this->assertSame('Late', $a->status);
    }

    public function test_admin_entry_uses_the_same_grace_rule(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        $emp = Employee::create([
            'department_id' => $dept->id, 'employee_id' => 'EMP1', 'name' => 'Person One',
            'email' => 'p1@example.com', 'designation' => 'Designer', 'salary' => 1000,
            'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true,
        ]);

        $this->post('/attendances', [
            'employee_id' => $emp->id, 'attendance_date' => '2026-11-10', 'status' => 'Present',
            'check_in' => '2026-11-10T19:09', 'check_out' => '2026-11-11T05:00',
        ]);
        $this->assertSame(0, (int) Attendance::first()->late_minutes);

        $this->post('/attendances', [
            'employee_id' => $emp->id, 'attendance_date' => '2026-11-11', 'status' => 'Late',
            'check_in' => '2026-11-11T19:30', 'check_out' => '2026-11-12T05:00',
        ]);
        $this->assertSame(30, (int) Attendance::latest('id')->first()->late_minutes);
    }
}
