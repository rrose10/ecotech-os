<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceEditTest extends TestCase
{
    use RefreshDatabase;

    private function record(): Attendance
    {
        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        $emp = Employee::create([
            'department_id' => $dept->id, 'employee_id' => 'EMP1', 'name' => 'Person One',
            'email' => 'p1@example.com', 'designation' => 'Designer', 'salary' => 1000,
            'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true,
        ]);

        return Attendance::create([
            'employee_id' => $emp->id, 'attendance_date' => '2026-11-10',
            'check_in' => '2026-11-10 19:00:00', 'check_out' => '2026-11-11 06:00:00',
            'working_minutes' => 0, 'late_minutes' => 0, 'status' => 'Present', 'shift' => 'Night',
        ]);
    }

    public function test_edit_page_opens_and_show_redirects_to_it(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $a = $this->record();

        $this->get("/attendances/{$a->id}/edit")->assertOk()->assertSee('Edit Attendance');
        $this->get("/attendances/{$a->id}")->assertRedirect("/attendances/{$a->id}/edit");
    }

    public function test_update_recalculates_late_and_working_minutes_across_midnight(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $a = $this->record();

        $this->put("/attendances/{$a->id}", [
            'employee_id' => $a->employee_id,
            'attendance_date' => '2026-11-10',
            'status' => 'Late',
            'check_in' => '2026-11-10T19:30',
            'check_out' => '2026-11-11T05:30',
        ])->assertRedirect(route('attendances.index'));

        $a->refresh();
        $this->assertSame(30, $a->late_minutes);
        $this->assertSame(600, $a->working_minutes);
    }
}
