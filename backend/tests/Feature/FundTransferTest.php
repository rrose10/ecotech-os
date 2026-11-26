<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\FundTransfer;
use App\Models\Income;
use App\Models\User;
use App\Support\AccountsReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundTransferTest extends TestCase
{
    use RefreshDatabase;

    private function accounts(): array
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $bank = BankAccount::create([
            'account_name' => 'City Bank', 'type' => 'Bank', 'bank_name' => 'City', 'account_number' => '1',
            'opening_balance' => 100000, 'current_balance' => 100000, 'is_active' => true,
        ]);
        $cash = BankAccount::create([
            'account_name' => 'Cash in Hand', 'type' => 'Cash',
            'opening_balance' => 0, 'current_balance' => 0, 'is_active' => true,
        ]);

        return [$bank, $cash];
    }

    private function transfer($from, $to, $amount): \Illuminate\Testing\TestResponse
    {
        return $this->post('/fund-transfers', [
            'from_account_id' => $from->id, 'to_account_id' => $to->id,
            'amount' => $amount, 'transfer_date' => '2026-11-10', 'remarks' => 'Monthly cash',
        ]);
    }

    public function test_transfer_moves_money_and_is_not_income_or_expense(): void
    {
        [$bank, $cash] = $this->accounts();

        $this->transfer($bank, $cash, 15000)->assertSessionHasNoErrors()->assertRedirect(route('fund-transfers.index'));

        $this->assertEquals(85000, $bank->fresh()->current_balance);
        $this->assertEquals(15000, $cash->fresh()->current_balance);
        $this->assertSame(1, FundTransfer::count());
        $this->assertSame(0, Income::count());
        $this->assertSame(0, Expense::count());
    }

    public function test_transfer_is_refused_when_the_source_has_too_little(): void
    {
        [$bank, $cash] = $this->accounts();

        $this->transfer($cash, $bank, 500)->assertSessionHas('error');

        $this->assertSame(0, FundTransfer::count());
        $this->assertEquals(100000, $bank->fresh()->current_balance);
        $this->assertEquals(0, $cash->fresh()->current_balance);
    }

    public function test_same_account_is_refused(): void
    {
        [$bank] = $this->accounts();

        $this->transfer($bank, $bank, 100)->assertSessionHasErrors('to_account_id');
        $this->assertSame(0, FundTransfer::count());
    }

    public function test_edit_recalculates_both_balances(): void
    {
        [$bank, $cash] = $this->accounts();
        $this->transfer($bank, $cash, 15000);
        $t = FundTransfer::firstOrFail();

        $this->put("/fund-transfers/{$t->id}", [
            'from_account_id' => $bank->id, 'to_account_id' => $cash->id,
            'amount' => 10000, 'transfer_date' => '2026-11-10',
        ])->assertSessionHasNoErrors();

        $this->assertEquals(90000, $bank->fresh()->current_balance);
        $this->assertEquals(10000, $cash->fresh()->current_balance);
    }

    public function test_delete_puts_the_money_back(): void
    {
        [$bank, $cash] = $this->accounts();
        $this->transfer($bank, $cash, 15000);
        $t = FundTransfer::firstOrFail();

        $this->delete("/fund-transfers/{$t->id}")->assertSessionHas('success');

        $this->assertSame(0, FundTransfer::count());
        $this->assertEquals(100000, $bank->fresh()->current_balance);
        $this->assertEquals(0, $cash->fresh()->current_balance);
    }

    public function test_delete_is_refused_when_the_cash_was_already_spent(): void
    {
        [$bank, $cash] = $this->accounts();
        $this->transfer($bank, $cash, 15000);
        $cash->update(['current_balance' => 2000]);   // 13,000 was spent
        $t = FundTransfer::firstOrFail();

        $this->delete("/fund-transfers/{$t->id}")->assertSessionHas('error');

        $this->assertSame(1, FundTransfer::count());
        $this->assertEquals(2000, $cash->fresh()->current_balance);
    }

    public function test_page_lists_transfers_and_team_members_are_blocked(): void
    {
        [$bank, $cash] = $this->accounts();
        $this->transfer($bank, $cash, 15000);

        $this->get('/fund-transfers')->assertOk()->assertSee('City Bank')->assertSee('Cash in Hand')->assertSee('15,000.00');

        $this->actingAs(User::factory()->create(['role' => 'employee']));
        $this->get('/fund-transfers')->assertForbidden();
        $this->post('/fund-transfers', [])->assertForbidden();
    }

    public function test_monthly_pdf_lists_the_transfer(): void
    {
        [$bank, $cash] = $this->accounts();
        $this->transfer($bank, $cash, 15000);

        $body = AccountsReport::build(11, 2026);

        $this->assertStringContainsString('Transfers between accounts', $body);
        $this->assertStringContainsString('15,000.00', $body);
        $this->assertStringContainsString('Monthly cash', $body);
    }
}
