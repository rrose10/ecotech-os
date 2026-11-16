<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturePackTest extends TestCase
{
    use RefreshDatabase;

    private static int $n = 0;

    private function employeeUser(array $employee = []): array
    {
        self::$n++;
        $user = User::factory()->create(['role' => 'employee']);
        $dept = Department::first() ?? Department::create(['name' => 'Design', 'code' => 'DSN']);

        $emp = Employee::create(array_merge([
            'department_id' => $dept->id,
            'employee_id' => 'EMP' . self::$n,
            'name' => 'Person ' . self::$n,
            'email' => 'p' . self::$n . '@example.com',
            'designation' => 'Designer',
            'salary' => 1000,
            'joining_date' => today()->subYears(2),
            'gender' => 'Male',
            'status' => true,
            'user_id' => $user->id,
        ], $employee));

        return [$user, $emp];
    }

    private function leaveType(int $days = 10): LeaveType
    {
        return LeaveType::create(['name' => 'Casual', 'days_per_year' => $days, 'is_paid' => true, 'status' => true]);
    }

    public function test_employee_can_request_leave_and_it_starts_pending(): void
    {
        [$user, $emp] = $this->employeeUser();
        $type = $this->leaveType();

        $this->actingAs($user)->post(route('portal.leaves.store'), [
            'leave_type_id' => $type->id,
            'from_date' => today()->addDays(2)->toDateString(),
            'to_date' => today()->addDays(4)->toDateString(),
            'reason' => 'Family event',
        ])->assertRedirect(route('portal.leaves'));

        $leave = Leave::first();
        $this->assertSame('Pending', $leave->status);
        $this->assertEquals(3, (int) $leave->total_days);
        $this->assertSame($emp->id, $leave->employee_id);

        $this->actingAs($user)->get(route('portal.leaves'))->assertOk()->assertSee('Casual');
    }

    public function test_overlapping_and_over_balance_requests_are_rejected(): void
    {
        [$user] = $this->employeeUser();
        $type = $this->leaveType(2);

        $this->actingAs($user)->post(route('portal.leaves.store'), [
            'leave_type_id' => $type->id,
            'from_date' => today()->addDays(5)->toDateString(),
            'to_date' => today()->addDays(6)->toDateString(),
        ])->assertSessionHasNoErrors();

        // overlaps the first request
        $this->post(route('portal.leaves.store'), [
            'leave_type_id' => $type->id,
            'from_date' => today()->addDays(6)->toDateString(),
            'to_date' => today()->addDays(7)->toDateString(),
        ])->assertSessionHasErrors('from_date');

        // 3 days when only 2 are allowed
        $this->post(route('portal.leaves.store'), [
            'leave_type_id' => $type->id,
            'from_date' => today()->addDays(20)->toDateString(),
            'to_date' => today()->addDays(22)->toDateString(),
        ])->assertSessionHasErrors('to_date');

        $this->assertSame(1, Leave::count());
    }

    public function test_employee_can_cancel_only_own_pending_leave(): void
    {
        [$user, $emp] = $this->employeeUser();
        [, $other] = $this->employeeUser();
        $type = $this->leaveType();

        $mine = Leave::create(['employee_id' => $emp->id, 'leave_type_id' => $type->id,
            'from_date' => today()->addDay(), 'to_date' => today()->addDay(), 'total_days' => 1, 'status' => 'Pending']);
        $theirs = Leave::create(['employee_id' => $other->id, 'leave_type_id' => $type->id,
            'from_date' => today()->addDay(), 'to_date' => today()->addDay(), 'total_days' => 1, 'status' => 'Pending']);
        $approved = Leave::create(['employee_id' => $emp->id, 'leave_type_id' => $type->id,
            'from_date' => today()->addDays(9), 'to_date' => today()->addDays(9), 'total_days' => 1, 'status' => 'Approved']);

        $this->actingAs($user)->delete(route('portal.leaves.cancel', $theirs))->assertNotFound();
        $this->delete(route('portal.leaves.cancel', $approved))->assertRedirect(route('portal.leaves'));
        $this->assertDatabaseHas('leaves', ['id' => $approved->id]);

        $this->delete(route('portal.leaves.cancel', $mine))->assertRedirect(route('portal.leaves'));
        $this->assertDatabaseMissing('leaves', ['id' => $mine->id]);
    }

    public function test_payslip_is_visible_only_to_its_owner(): void
    {
        [$user, $emp] = $this->employeeUser();
        [, $other] = $this->employeeUser();
        $admin = User::factory()->create(['role' => 'admin']);
        $bank = BankAccount::create(['account_name' => 'Main', 'type' => 'Cash']);

        $make = fn ($e, $m) => Payroll::create([
            'employee_id' => $e->id, 'bank_account_id' => $bank->id, 'month' => $m, 'year' => 2026,
            'basic_salary' => 1000, 'bonus' => 0, 'total_salary' => 1000, 'net_salary' => 1000,
            'status' => 'Pending', 'created_by' => $admin->id,
        ]);

        $mine = $make($emp, 1);
        $theirs = $make($other, 2);

        $this->actingAs($user)->get(route('portal.payslip', $mine))
            ->assertOk()->assertSee('Payslip')->assertSee($emp->name);
        $this->get(route('portal.payslip', $theirs))->assertNotFound();
    }

    public function test_user_with_pending_password_change_is_redirected_until_they_set_one(): void
    {
        [$user] = $this->employeeUser();
        $user->forceFill(['must_change_password' => true])->save();

        $this->actingAs($user)->get(route('portal.dashboard'))->assertRedirect(route('password.force'));
        $this->get(route('password.force'))->assertOk();

        // too weak
        $this->post(route('password.force.update'), ['password' => 'short', 'password_confirmation' => 'short'])
            ->assertSessionHasErrors('password');

        $this->post(route('password.force.update'), [
            'password' => 'BrandNew2026', 'password_confirmation' => 'BrandNew2026',
        ])->assertRedirect('/dashboard');

        $this->assertFalse($user->fresh()->must_change_password);
        $this->get(route('portal.dashboard'))->assertOk();
    }

    public function test_banner_shows_birthday_and_work_anniversary_today(): void
    {
        [$user] = $this->employeeUser();
        $this->employeeUser(['name' => 'Cake Person', 'date_of_birth' => today()->subYears(30)]);
        $this->employeeUser(['name' => 'Veteran Person', 'joining_date' => today()->subYears(3)]);

        $this->actingAs($user)->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee("Cake Person's birthday", false)
            ->assertSee('Veteran Person completes 3 years');
    }
}
