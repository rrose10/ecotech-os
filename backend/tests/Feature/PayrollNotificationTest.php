<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\Payroll;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function member(): array
    {
        $user = User::factory()->create(['role' => 'employee']);
        $dept = Department::create(['name' => 'Design', 'code' => 'DSN']);
        $emp = Employee::create([
            'user_id' => $user->id, 'department_id' => $dept->id, 'employee_id' => 'EMP1',
            'name' => 'Person One', 'email' => 'p1@example.com', 'designation' => 'Designer',
            'basic_salary' => 20000, 'joining_date' => today()->subYear(), 'gender' => 'Male', 'status' => true,
        ]);

        return [$user, $emp];
    }

    public function test_marking_payroll_paid_notifies_the_member_once(): void
    {
        [$user, $emp] = $this->member();
        ExpenseCategory::create(['name' => 'Salary', 'status' => true]);
        $bank = BankAccount::create([
            'account_name' => 'Cash', 'type' => 'Cash', 'opening_balance' => 100000,
            'current_balance' => 100000, 'is_active' => true,
        ]);
        $payroll = Payroll::create([
            'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'month' => 11, 'year' => 2026,
            'basic_salary' => 20000, 'bonus' => 0, 'advance_amount' => 0, 'loan_amount' => 0,
            'total_salary' => 20000, 'net_salary' => 20000, 'status' => 'Pending',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $data = [
            'employee_id' => $emp->id, 'bank_account_id' => $bank->id, 'month' => 11, 'year' => 2026,
            'bonus' => 0, 'payment_date' => '2026-11-30', 'status' => 'Paid',
        ];

        $this->put("/payrolls/{$payroll->id}", $data)->assertSessionHasNoErrors();
        $this->assertSame(1, PortalNotification::where('user_id', $user->id)->count());

        // Saving an already-paid payroll again must not notify twice.
        $this->put("/payrolls/{$payroll->id}", $data);
        $this->assertSame(1, PortalNotification::where('user_id', $user->id)->count());
    }

    public function test_member_sees_and_opens_own_notification_only(): void
    {
        [$user, $emp] = $this->member();
        $payroll = Payroll::create([
            'employee_id' => $emp->id, 'month' => 11, 'year' => 2026, 'basic_salary' => 20000,
            'bonus' => 0, 'advance_amount' => 0, 'loan_amount' => 0,
            'total_salary' => 20000, 'net_salary' => 20000, 'status' => 'Paid',
        ]);
        $n = PortalNotification::salaryPaid($payroll);

        $this->actingAs($user)->get('/portal/dashboard')->assertOk()->assertSee('Notifications');
        $this->get('/portal/notifications')->assertOk()->assertSee('November 2026');

        $this->get("/portal/notifications/{$n->id}")
            ->assertRedirect(route('portal.payslip', $payroll, false));
        $this->assertNotNull($n->fresh()->read_at);

        $other = User::factory()->create(['role' => 'employee']);
        $this->actingAs($other)->get("/portal/notifications/{$n->id}")->assertNotFound();
    }
}
