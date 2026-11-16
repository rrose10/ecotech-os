<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_portal_is_english_by_default(): void
    {
        $this->actingAs($this->teamUser())
            ->get('/portal/dashboard')
            ->assertOk()
            ->assertSee('My attendance')
            ->assertDontSee('আমার হাজিরা');
    }

    public function test_switching_to_bangla_translates_the_portal_menu_and_pages(): void
    {
        $user = $this->teamUser();

        $this->actingAs($user)->from('/portal/dashboard')->post('/locale/bn')->assertRedirect('/portal/dashboard');

        $this->get('/portal/dashboard')->assertOk()
            ->assertSee('আমার হাজিরা')
            ->assertSee('আমার বেতন')
            ->assertSee('স্বাগতম, Person One')
            ->assertSee('English');

        $this->get('/portal/attendance')->assertOk()->assertSee('আজকের হাজিরা');
        $this->get('/portal/salary')->assertOk()->assertSee('বেতনের ইতিহাস');
        $this->get('/portal/leaves')->assertOk();
    }

    public function test_switching_back_to_english_works_and_bad_codes_are_rejected(): void
    {
        $this->actingAs($this->teamUser());

        $this->post('/locale/bn');
        $this->post('/locale/en');
        $this->get('/portal/dashboard')->assertSee('My attendance');

        $this->post('/locale/xx')->assertNotFound();
    }

    public function test_the_choice_is_remembered_in_a_cookie(): void
    {
        $this->post('/locale/bn')->assertCookie('locale', 'bn');
    }
}
