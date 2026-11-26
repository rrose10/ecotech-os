<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\ExpenseCategory;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanSkipTest extends TestCase
{
    use RefreshDatabase;

    private function setupLoan(): array
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin);

        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        $emp = Employee::create([
            'department_id' => $dept->id, 'employee_id' => 'EMP1', 'name' => 'Person One',
            'email' => 'p1@example.com', 'designation' => 'Designer', 'basic_salary' => 5000,
            'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true,
        ]);
        $bank = BankAccount::create([
            'account_name' => 'Main', 'type' => 'Bank', 'opening_balance' => 50000,
            'current_balance' => 50000, 'is_active' => true,
        ]);
        ExpenseCategory::firstOrCreate(['name' => 'Salary'], ['status' => true]);

        $this->post('/employee-loans', [
            'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'loan_amount' => 3000,
            'monthly_installment' => 1000, 'total_installments' => 3, 'loan_date' => '2026-01-05',
            'start_month' => 1, 'start_year' => 2026, 'end_month' => '', 'end_year' => '',
        ]);

        return [$emp, $bank, EmployeeLoan::firstOrFail()];
    }

    public function test_skipped_month_collects_nothing_and_the_last_installment_moves_later(): void
    {
        [$emp, $bank, $loan] = $this->setupLoan();
        $this->assertSame(3, (int) $loan->end_month);   // Jan, Feb, Mar

        $this->post("/employee-loans/{$loan->id}/skip", ['month' => 2, 'year' => 2026, 'reason' => 'Eid'])
            ->assertSessionHasNoErrors();

        $loan->refresh();
        $this->assertSame(4, (int) $loan->end_month);   // now ends in April
        $this->assertSame(1, $loan->skips()->count());

        $this->post('/payrolls', [
            'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'month' => 2, 'year' => 2026,
            'bonus' => 0, 'payment_date' => '2026-02-28', 'status' => 'Paid',
        ]);

        $payroll = Payroll::firstOrFail();
        $this->assertEquals(0, $payroll->loan_amount);
        $this->assertSame(0, (int) $loan->fresh()->paid_installments);
        $this->assertEquals(3000, $loan->fresh()->remaining_balance);
    }

    public function test_other_months_still_collect_the_installment(): void
    {
        [$emp, $bank, $loan] = $this->setupLoan();
        $this->post("/employee-loans/{$loan->id}/skip", ['month' => 2, 'year' => 2026]);

        $this->post('/payrolls', [
            'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'month' => 3, 'year' => 2026,
            'bonus' => 0, 'payment_date' => '2026-03-31', 'status' => 'Paid',
        ]);

        $this->assertEquals(1000, Payroll::firstOrFail()->loan_amount);
        $this->assertSame(1, (int) $loan->fresh()->paid_installments);
    }

    public function test_calculate_leaves_out_a_skipped_month(): void
    {
        [$emp, , $loan] = $this->setupLoan();
        $this->post("/employee-loans/{$loan->id}/skip", ['month' => 2, 'year' => 2026]);

        $url = '/payrolls/calculate?employee_id=' . $emp->id . '&bonus=0';
        $this->assertSame('0.00', $this->getJson("$url&month=2&year=2026")->json('loan_amount'));
        $this->assertSame('1000.00', $this->getJson("$url&month=3&year=2026")->json('loan_amount'));
    }

    public function test_cannot_skip_a_month_that_is_already_paid_or_outside_the_loan(): void
    {
        [$emp, $bank, $loan] = $this->setupLoan();

        $this->post('/payrolls', [
            'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'month' => 1, 'year' => 2026,
            'bonus' => 0, 'payment_date' => '2026-01-31', 'status' => 'Paid',
        ]);

        $this->post("/employee-loans/{$loan->id}/skip", ['month' => 1, 'year' => 2026])->assertSessionHasErrors();
        $this->post("/employee-loans/{$loan->id}/skip", ['month' => 9, 'year' => 2026])->assertSessionHasErrors();
        $this->assertSame(0, $loan->skips()->count());
        $this->assertSame(3, (int) $loan->fresh()->end_month);
    }

    public function test_undoing_a_skip_brings_the_end_month_back(): void
    {
        [, , $loan] = $this->setupLoan();
        $this->post("/employee-loans/{$loan->id}/skip", ['month' => 2, 'year' => 2026]);
        $skip = $loan->skips()->first();

        $this->delete("/employee-loans/{$loan->id}/skips/{$skip->id}")->assertSessionHasNoErrors();

        $this->assertSame(0, $loan->skips()->count());
        $this->assertSame(3, (int) $loan->fresh()->end_month);
    }

    public function test_team_members_cannot_skip(): void
    {
        [, , $loan] = $this->setupLoan();
        $this->actingAs(User::factory()->create(['role' => 'employee']));

        $this->post("/employee-loans/{$loan->id}/skip", ['month' => 2, 'year' => 2026])->assertForbidden();
        $this->assertSame(0, $loan->skips()->count());
    }
}
