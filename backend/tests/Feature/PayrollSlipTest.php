<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollSlipTest extends TestCase
{
    use RefreshDatabase;

    private function bank(): BankAccount
    {
        return BankAccount::create([
            'account_name' => 'Cash', 'type' => 'Cash', 'opening_balance' => 100000,
            'current_balance' => 100000, 'is_active' => true,
        ]);
    }

    public function test_slip_and_list_show_advance_loan_and_net_salary(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin);
        $bank = $this->bank();
        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        $emp = Employee::create([
            'department_id' => $dept->id, 'employee_id' => 'EMP1', 'name' => 'Person One',
            'email' => 'p1@example.com', 'designation' => 'Designer', 'basic_salary' => 20000,
            'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true,
        ]);
        $p = Payroll::create([
            'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'created_by' => $admin->id, 'month' => 11, 'year' => 2026, 'basic_salary' => 20000,
            'bonus' => 0, 'advance_amount' => 5000, 'loan_amount' => 1666.67,
            'total_salary' => 20000, 'net_salary' => 13333.33, 'status' => 'Pending',
        ]);

        $this->get("/payrolls/{$p->id}")->assertOk()
            ->assertSee('Advance Salary Deducted')->assertSee('Loan Installment Deducted')
            ->assertSee('Net Salary')->assertSee('5,000.00')->assertSee('1,666.67')->assertSee('13,333.33');

        $this->get('/payrolls')->assertOk()->assertSee('Deductions')->assertSee('13,333.33');
    }

    public function test_all_slips_of_a_month_open_on_one_page(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin);
        $bank = $this->bank();
        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);

        foreach (['One', 'Two'] as $i => $name) {
            $emp = Employee::create([
                'department_id' => $dept->id, 'employee_id' => 'EMP'.$i, 'name' => 'Person '.$name,
                'email' => "p{$i}@example.com", 'designation' => 'Designer', 'basic_salary' => 20000,
                'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true,
            ]);
            Payroll::create([
                'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'created_by' => $admin->id, 'month' => 11, 'year' => 2026, 'basic_salary' => 20000,
                'bonus' => 0, 'advance_amount' => 0, 'loan_amount' => 0,
                'total_salary' => 20000, 'net_salary' => 20000, 'status' => $i === 0 ? 'Paid' : 'Pending',
            ]);
        }

        $this->get('/payrolls/slips?month=11&year=2026')->assertOk()
            ->assertSee('Person One')->assertSee('Person Two')->assertSee('slip-page');

        $this->get('/payrolls/slips?month=11&year=2026&status=Paid')->assertOk()
            ->assertSee('Person One')->assertDontSee('Person Two');

        $this->get('/payrolls/slips?month=10&year=2026')->assertOk()
            ->assertSee('No salary slips for this month yet.');

        $this->get('/payrolls')->assertOk()->assertSee('All slips (PDF)');
    }
}
