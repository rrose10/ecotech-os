<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalBirthdayTest extends TestCase
{
    use RefreshDatabase;

    private function employee(string $name, ?string $dob, ?User $user = null, ?string $photo = null): Employee
    {
        static $n = 0;
        $n++;
        $dept = Department::first() ?? Department::create(['name' => 'Design', 'code' => 'DSN']);

        return Employee::create([
            'department_id' => $dept->id,
            'employee_id' => 'EMP' . $n,
            'name' => $name,
            'email' => "emp{$n}@example.com",
            'designation' => 'Designer',
            'salary' => 1000,
            'joining_date' => today()->subYear(),
            'date_of_birth' => $dob,
            'gender' => 'Male',
            'photo' => $photo,
            'status' => true,
            'user_id' => $user?->id,
        ]);
    }

    public function test_portal_dashboard_lists_team_birthdays(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $this->employee('Me Myself', '1995-03-01', $user);
        $this->employee('Birthday Today', today()->subYears(25)->toDateString());
        $this->employee('Soon Person', today()->addDays(3)->subYears(30)->toDateString());

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Upcoming Birthdays')
            ->assertSee('Birthday Today')
            ->assertSee('Soon Person');
    }

    public function test_topbar_shows_employee_photo_when_present(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $this->employee('Pic Person', '1990-01-01', $user, 'employees/pic.jpg');

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('storage/employees/pic.jpg', false);
    }
}
