<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\User;
use App\Support\AccountsReport;
use App\Support\SimplePdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AccountsPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(AccountsReport::directory());
        parent::tearDown();
    }

    private function seedMonth(): void
    {
        $bank = BankAccount::create([
            'account_name' => 'Main', 'type' => 'Bank', 'opening_balance' => 1000,
            'current_balance' => 1000, 'is_active' => true,
        ]);
        $ic = IncomeCategory::create(['name' => 'Sales', 'status' => true]);
        $ec = ExpenseCategory::create(['name' => 'Rent', 'status' => true]);

        Income::create([
            'income_category_id' => $ic->id, 'bank_account_id' => $bank->id, 'title' => 'Order batch',
            'amount_bdt' => 50000, 'amount_usd' => 0, 'income_date' => '2026-10-10', 'payment_method' => 'Bank',
        ]);
        Expense::create([
            'expense_category_id' => $ec->id, 'bank_account_id' => $bank->id, 'title' => 'Office rent',
            'amount' => 12000, 'expense_date' => '2026-10-05', 'payment_method' => 'Bank',
        ]);
    }

    public function test_simple_pdf_is_a_valid_pdf_with_page_numbers(): void
    {
        $pdf = new SimplePdf('Test');
        $pdf->text(40, 60, 'Hello (world)');
        $pdf->addPage();
        $pdf->setFooter('Page {p} of {n}');
        $out = $pdf->output();

        $this->assertStringStartsWith('%PDF-1.4', $out);
        $this->assertStringContainsString('%%EOF', $out);
        $this->assertStringContainsString('/Count 2', $out);
        $this->assertStringContainsString('Page 2 of 2', $out);
    }

    public function test_admin_downloads_a_pdf_with_the_months_figures(): void
    {
        $this->seedMonth();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $response = $this->get('/reports/accounts-pdf?month=10&year=2026');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $body = $response->getContent();
        $this->assertStringStartsWith('%PDF', $body);
        $this->assertStringContainsString('50,000.00', $body);
        $this->assertStringContainsString('12,000.00', $body);
        $this->assertStringContainsString('38,000.00', $body);   // net result
        $this->assertStringContainsString('Office rent', $body);
    }

    public function test_command_saves_a_file_and_the_page_lists_and_serves_it(): void
    {
        $this->seedMonth();
        $this->artisan('reports:monthly', ['--month' => 10, '--year' => 2026])->assertExitCode(0);

        $this->assertFileExists(AccountsReport::directory() . '/accounts-2026-10.pdf');

        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->get('/reports')->assertOk()->assertSee('October 2026');
        $this->get('/reports/accounts-pdf/saved/accounts-2026-10.pdf')->assertOk();
    }

    public function test_save_all_starts_from_the_first_entry(): void
    {
        $this->seedMonth();
        $this->artisan('reports:monthly', ['--all' => true])->assertExitCode(0);

        $this->assertFileExists(AccountsReport::directory() . '/accounts-2026-10.pdf');
    }

    public function test_saved_download_rejects_bad_file_names_and_team_members(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->get('/reports/accounts-pdf/saved/..%2F.env')->assertNotFound();
        $this->get('/reports/accounts-pdf/saved/secret.pdf')->assertNotFound();

        $this->actingAs(User::factory()->create(['role' => 'employee']));
        $this->get('/reports/accounts-pdf?month=10&year=2026')->assertForbidden();
    }
}
