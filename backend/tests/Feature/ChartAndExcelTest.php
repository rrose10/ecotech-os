<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SimpleXlsx;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class ChartAndExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_the_income_vs_expense_chart(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->get('/dashboard')->assertOk()
            ->assertSee('Income vs expense')
            ->assertSee('No income or expense recorded in the last 6 months.');
    }

    public function test_excel_exports_download_as_xlsx(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        foreach (['finance', 'attendance', 'payroll'] as $report) {
            $response = $this->get("/reports/{$report}?export=xlsx");
            $response->assertOk();
            $this->assertStringContainsString(
                'spreadsheetml.sheet',
                $response->headers->get('Content-Type')
            );
        }

        $this->get('/reports/finance?export=csv')->assertOk();
    }

    public function test_xlsx_builder_writes_a_valid_workbook(): void
    {
        $path = SimpleXlsx::build([['Name', 'Amount'], ['Rose & Co', 1234.5], ['Zero', '007']], 'Test');

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path));
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($path);

        $this->assertStringContainsString('Rose &amp; Co', $sheet);
        $this->assertStringContainsString('<v>1234.5</v>', $sheet);
        // IDs with leading zeros stay text
        $this->assertStringContainsString('007', $sheet);
        $this->assertStringNotContainsString('<v>7</v>', $sheet);
    }
}
