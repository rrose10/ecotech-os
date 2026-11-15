<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PayrollSyncTest extends TestCase
{
    use RefreshDatabase;

    private $bankA;
    private $bankB;
    private int $empId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $dept = DB::table('departments')->insertGetId([
            'name' => 'IT', 'code' => 'IT', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->empId = DB::table('employees')->insertGetId([
            'department_id' => $dept, 'employee_id' => 'E1', 'name' => 'Rahim',
            'email' => 'rahim@example.com', 'designation' => 'Dev', 'basic_salary' => 20000,
            'joining_date' => '2026-01-01', 'status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        ExpenseCategory::create(['name' => 'Salary']);

        $this->bankA = BankAccount::create([
            'account_name' => 'A', 'type' => 'Bank',
            'opening_balance' => 100000, 'current_balance' => 100000, 'is_active' => true,
        ]);

        $this->bankB = BankAccount::create([
            'account_name' => 'B', 'type' => 'Cash',
            'opening_balance' => 50000, 'current_balance' => 50000, 'is_active' => true,
        ]);
    }

    private function data(array $o = []): array
    {
        return array_merge([
            'employee_id' => $this->empId, 'bank_account_id' => $this->bankA->id,
            'month' => 7, 'year' => 2026, 'bonus' => 3000,
            'status' => 'Paid', 'payment_date' => '2026-07-30',
        ], $o);
    }

    private function bal($bank): float
    {
        return (float) $bank->fresh()->current_balance;
    }

    public function test_paid_to_paid_does_not_double_deduct_and_can_change_bank(): void
    {
        $this->post('/payrolls', $this->data())->assertSessionHasNoErrors();
        $payroll = Payroll::first();
        $this->assertEquals(77000, $this->bal($this->bankA));

        $this->put("/payrolls/{$payroll->id}", $this->data(['bonus' => 5000]))->assertSessionHasNoErrors();
        $this->assertEquals(75000, $this->bal($this->bankA));
        $this->assertSame(1, Expense::count());
        $this->assertEquals(25000, (float) Expense::first()->amount);

        $this->put("/payrolls/{$payroll->id}", $this->data([
            'bonus' => 0, 'bank_account_id' => $this->bankB->id,
        ]))->assertSessionHasNoErrors();

        $this->assertEquals(100000, $this->bal($this->bankA));
        $this->assertEquals(30000, $this->bal($this->bankB));
        $this->assertSame(1, Expense::count());
        $this->assertSame('Cash', Expense::first()->payment_method);
    }

    public function test_insufficient_balance_changes_nothing(): void
    {
        $this->post('/payrolls', $this->data())->assertSessionHasNoErrors();
        $payroll = Payroll::first();

        $small = BankAccount::create([
            'account_name' => 'C', 'type' => 'Cash',
            'opening_balance' => 5000, 'current_balance' => 5000, 'is_active' => true,
        ]);

        $this->put("/payrolls/{$payroll->id}", $this->data(['bank_account_id' => $small->id]))
            ->assertSessionHasErrors('error');

        $this->assertEquals(77000, $this->bal($this->bankA));
        $this->assertEquals(5000, $this->bal($small));
        $this->assertSame($this->bankA->id, (int) $payroll->fresh()->bank_account_id);
    }

    public function test_pending_paid_pending_and_delete_restore_balance(): void
    {
        $this->post('/payrolls', $this->data(['status' => 'Pending']))->assertSessionHasNoErrors();
        $payroll = Payroll::first();
        $this->assertEquals(100000, $this->bal($this->bankA));
        $this->assertSame(0, Expense::count());

        $this->put("/payrolls/{$payroll->id}", $this->data())->assertSessionHasNoErrors();
        $this->assertEquals(77000, $this->bal($this->bankA));
        $this->assertSame(1, Expense::count());

        $this->put("/payrolls/{$payroll->id}", $this->data(['status' => 'Pending']))->assertSessionHasNoErrors();
        $this->assertEquals(100000, $this->bal($this->bankA));
        $this->assertSame(0, Expense::count());

        $this->put("/payrolls/{$payroll->id}", $this->data())->assertSessionHasNoErrors();
        $this->delete("/payrolls/{$payroll->id}")->assertSessionHasNoErrors();

        $this->assertEquals(100000, $this->bal($this->bankA));
        $this->assertSame(0, Expense::count());
        $this->assertSame(0, Payroll::count());
    }

    public function test_salary_expense_cannot_be_edited_or_deleted_from_expense_module(): void
    {
        $this->post('/payrolls', $this->data())->assertSessionHasNoErrors();
        $expense = Expense::first();

        $this->delete("/expenses/{$expense->id}")->assertSessionHas('error');

        $this->assertSame(1, Expense::count());
        $this->assertEquals(77000, $this->bal($this->bankA));
    }
}
