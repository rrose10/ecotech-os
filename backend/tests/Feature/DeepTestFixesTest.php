<?php

namespace Tests\Feature;

use App\Models\AdvanceSalary;
use App\Models\Attendance;
use App\Models\BankAccount;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\ExpenseCategory;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeepTestFixesTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(): array
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        $emp = Employee::create([
            'department_id' => $dept->id, 'employee_id' => 'EMP1', 'name' => 'Person One',
            'email' => 'p1@example.com', 'designation' => 'Designer', 'salary' => 1000,
            'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true,
        ]);
        $bank = BankAccount::create([
            'account_name' => 'Main', 'type' => 'Bank', 'opening_balance' => 10000,
            'current_balance' => 10000, 'is_active' => true,
        ]);

        return [$emp, $bank];
    }

    public function test_duplicate_attendance_shows_an_error_instead_of_crashing(): void
    {
        [$emp] = $this->setupData();
        $payload = ['employee_id' => $emp->id, 'attendance_date' => '2026-01-05', 'status' => 'Present'];

        $this->post('/attendances', $payload)->assertRedirect(route('attendances.index'));
        $this->post('/attendances', $payload)->assertSessionHasErrors('attendance_date');
        $this->assertSame(1, Attendance::count());
    }

    public function test_attendance_store_computes_night_shift_minutes(): void
    {
        [$emp] = $this->setupData();

        $this->post('/attendances', [
            'employee_id' => $emp->id, 'attendance_date' => '2026-01-05', 'status' => 'Late',
            'check_in' => '2026-01-05T19:30', 'check_out' => '2026-01-06T05:30',
        ])->assertRedirect(route('attendances.index'));

        $a = Attendance::first();
        $this->assertSame(30, $a->late_minutes);
        $this->assertSame(600, $a->working_minutes);
    }

    public function test_advance_salary_works_without_a_preseeded_category(): void
    {
        [$emp, $bank] = $this->setupData();

        $this->post('/advance-salaries', [
            'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'amount' => 100,
            'advance_date' => today()->toDateString(), 'deduction_month' => 12, 'deduction_year' => 2026,
        ])->assertRedirect();

        $this->assertSame(1, AdvanceSalary::count());
        $this->assertTrue(ExpenseCategory::where('name', 'Advance Salary')->exists());
        $this->assertEquals(9900, $bank->fresh()->current_balance);
    }

    public function test_loan_works_without_category_and_fills_end_month(): void
    {
        [$emp, $bank] = $this->setupData();

        $this->post('/employee-loans', [
            'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'loan_amount' => 1000,
            'monthly_installment' => 200, 'total_installments' => 5, 'loan_date' => today()->toDateString(),
            'start_month' => 11, 'start_year' => 2026, 'end_month' => '', 'end_year' => '',
        ])->assertRedirect();

        $loan = EmployeeLoan::first();
        $this->assertNotNull($loan);
        $this->assertSame(3, (int) $loan->end_month);   // Nov + 4 months = Mar
        $this->assertSame(2027, (int) $loan->end_year);
        $this->assertTrue(ExpenseCategory::where('name', 'Employee Loan')->exists());
    }

    public function test_installer_adds_default_leave_types(): void
    {
        $this->artisan('ecotech:install')->assertSuccessful();
        $this->assertTrue(LeaveType::where('name', 'Casual Leave')->exists());
        $this->assertTrue(LeaveType::where('name', 'Sick Leave')->exists());
    }

    private function leaveType(int $days = 5): LeaveType
    {
        return LeaveType::create(['name' => 'Casual', 'days_per_year' => $days, 'is_paid' => true, 'status' => true]);
    }

    public function test_overlapping_leave_is_refused(): void
    {
        [$emp] = $this->setupData();
        $type = $this->leaveType();
        $p = ['employee_id' => $emp->id, 'leave_type_id' => $type->id, 'from_date' => '2026-12-01', 'to_date' => '2026-12-02'];

        $this->post('/leaves', $p)->assertRedirect(route('leaves.index'));
        $this->post('/leaves', array_merge($p, ['from_date' => '2026-12-02', 'to_date' => '2026-12-04']))
            ->assertSessionHasErrors('from_date');
        $this->assertSame(1, Leave::count());
    }

    public function test_approving_more_days_than_left_is_refused_cleanly(): void
    {
        [$emp] = $this->setupData();
        $type = $this->leaveType(5);
        $this->post('/leaves', ['employee_id' => $emp->id, 'leave_type_id' => $type->id,
            'from_date' => '2026-12-01', 'to_date' => '2026-12-20']);

        $leave = Leave::first();
        $this->post("/leaves/{$leave->id}/approve")->assertRedirect(route('leaves.index'))->assertSessionHas('error');

        $this->assertSame('Pending', $leave->fresh()->status);
    }

    public function test_rejecting_or_deleting_an_approved_leave_gives_days_back(): void
    {
        [$emp] = $this->setupData();
        $type = $this->leaveType(5);
        $this->post('/leaves', ['employee_id' => $emp->id, 'leave_type_id' => $type->id,
            'from_date' => '2026-12-01', 'to_date' => '2026-12-02']);
        $leave = Leave::first();

        $this->post("/leaves/{$leave->id}/approve");
        $this->assertSame(3, LeaveBalance::first()->remaining_days);

        $this->post("/leaves/{$leave->id}/reject");
        $this->assertSame(5, LeaveBalance::first()->remaining_days);

        // approve is only for pending ones, so a rejected leave cannot be approved twice
        $this->post("/leaves/{$leave->id}/approve")->assertSessionHas('error');
        $this->assertSame(5, LeaveBalance::first()->remaining_days);
    }
}
