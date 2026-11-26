<?php

namespace App\Support;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\Income;
use App\Models\EmployeeLoan;
use App\Models\LoanSkip;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Monthly accounts as a PDF: income, expenses, payroll, loans, bank balances.
 */
class AccountsReport
{
    private const LEFT  = 40;
    private const RIGHT = 555;
    private const TOP   = 50;
    private const LIMIT = 800;

    private SimplePdf $pdf;
    private float $y;

    public static function directory(): string
    {
        return storage_path('app/backups/reports');
    }

    public const ALL_TIME_FILE = 'accounts-all-time.pdf';

    public static function fileName(int $month, int $year): string
    {
        return sprintf('accounts-%04d-%02d.pdf', $year, $month);
    }

    /** Create and store the PDF of one month. Returns the file path. */
    public static function save(int $month, int $year): string
    {
        File::ensureDirectoryExists(self::directory());

        $path = self::directory() . '/' . self::fileName($month, $year);
        File::put($path, self::build($month, $year));

        return $path;
    }

    /** Save every finished month that has any income, expense or payroll. Returns how many. */
    public static function saveAll(): int
    {
        $dates = array_filter([
            Income::min('income_date'),
            Expense::min('expense_date'),
            Payroll::query()->selectRaw('MIN(year * 100 + month) as ym')->value('ym'),
        ]);

        self::saveAllTime();

        if (!$dates) {
            return 0;
        }

        $first = collect($dates)->map(function ($d) {
            return is_numeric($d) && (int) $d > 190000
                ? Carbon::create(intdiv((int) $d, 100), (int) $d % 100, 1)
                : Carbon::parse($d)->startOfMonth();
        })->min();

        $cursor = $first->copy();
        $last   = now()->subMonthNoOverflow()->startOfMonth();
        $count  = 0;

        while ($cursor->lte($last)) {
            self::save($cursor->month, $cursor->year);
            $count++;
            $cursor->addMonthNoOverflow();
        }

        return $count;
    }

    /** Saved PDFs, newest month first: [['file' => ..., 'label' => 'November 2026', 'size' => bytes]]. */
    public static function saved(): array
    {
        if (!is_dir(self::directory())) {
            return [];
        }

        $files = collect(File::files(self::directory()))
            ->filter(fn ($f) => preg_match('/^accounts-(\d{4})-(\d{2})\.pdf$/', $f->getFilename()))
            ->map(function ($f) {
                preg_match('/^accounts-(\d{4})-(\d{2})\.pdf$/', $f->getFilename(), $m);

                return [
                    'file'  => $f->getFilename(),
                    'label' => Carbon::create((int) $m[1], (int) $m[2], 1)->format('F Y'),
                    'size'  => $f->getSize(),
                ];
            })
            ->sortByDesc('file')
            ->values();

        $list = $files->all();

        $all = self::directory() . '/' . self::ALL_TIME_FILE;
        if (is_file($all)) {
            array_unshift($list, ['file' => self::ALL_TIME_FILE, 'label' => 'All time (everything so far)', 'size' => filesize($all)]);
        }

        return $list;
    }

    /** Create and store the all-time PDF. Returns the file path. */
    public static function saveAllTime(): string
    {
        File::ensureDirectoryExists(self::directory());

        $path = self::directory() . '/' . self::ALL_TIME_FILE;
        File::put($path, self::buildAllTime());

        return $path;
    }

    public static function buildAllTime(): string
    {
        return (new self())->renderAllTime();
    }

    public static function build(int $month, int $year): string
    {
        return (new self())->render($month, $year);
    }

    private function render(int $month, int $year): string
    {
        $this->pdf = new SimplePdf("EcoTech accounts {$month}/{$year}");
        $this->y   = self::TOP;

        $label = Carbon::create($year, $month, 1)->format('F Y');

        $incomes = Income::with('incomeCategory')
            ->whereYear('income_date', $year)->whereMonth('income_date', $month)
            ->orderBy('income_date')->get();
        $expenses = Expense::with('expenseCategory')
            ->whereYear('expense_date', $year)->whereMonth('expense_date', $month)
            ->orderBy('expense_date')->get();
        $payrolls = Payroll::with('employee')
            ->where('month', $month)->where('year', $year)
            ->get()->sortBy(fn ($p) => $p->employee->name ?? '')->values();

        $incomeBdt = (float) $incomes->sum('amount_bdt');
        $incomeUsd = (float) $incomes->sum('amount_usd');
        $expenseTotal = (float) $expenses->sum('amount');

        // Title
        $this->pdf->box(self::LEFT, $this->y, self::RIGHT - self::LEFT, 34, 0.12);
        $this->pdf->text(self::LEFT + 10, $this->y + 22, 'EcoTech - Monthly Accounts', 16, true);
        $this->pdf->text(self::RIGHT - 10, $this->y + 22, $label, 14, true, 'right');
        $this->y += 34 + 8;
        $this->pdf->text(self::LEFT, $this->y + 8, 'Created ' . now()->format('d M Y, h:i A') . '. Amounts are in Taka (Tk) unless marked USD.', 8);
        $this->y += 24;

        // Summary
        $this->heading('Summary');
        $this->row([['Total income (Tk)', 330], [$this->money($incomeBdt), 185, 'right']]);
        $this->row([['Total income (USD)', 330], ['USD ' . $this->money($incomeUsd), 185, 'right']]);
        $this->row([['Total expenses (Tk)', 330], [$this->money($expenseTotal), 185, 'right']]);
        $this->row([['Net result (Tk)', 330, 'left', true], [$this->money($incomeBdt - $expenseTotal), 185, 'right', true]], 0.93);
        $this->y += 10;

        // Income by category
        $this->heading('Income by category');
        $this->row([['Category', 260, 'left', true], ['Tk', 130, 'right', true], ['USD', 125, 'right', true]], 0.9);
        $groups = $incomes->groupBy(fn ($i) => $i->incomeCategory->name ?? 'Uncategorised')
            ->map(fn ($g) => ['bdt' => (float) $g->sum('amount_bdt'), 'usd' => (float) $g->sum('amount_usd')])
            ->sortByDesc('bdt');
        foreach ($groups as $name => $v) {
            $this->row([[$name, 260], [$this->money($v['bdt']), 130, 'right'], [$this->money($v['usd']), 125, 'right']]);
        }
        if ($groups->isEmpty()) {
            $this->note('No income this month.');
        }
        $this->y += 10;

        // Expense by category
        $this->heading('Expenses by category');
        $this->row([['Category', 385, 'left', true], ['Tk', 130, 'right', true]], 0.9);
        $groups = $expenses->groupBy(fn ($e) => $e->expenseCategory->name ?? 'Uncategorised')
            ->map(fn ($g) => (float) $g->sum('amount'))->sortDesc();
        foreach ($groups as $name => $v) {
            $this->row([[$name, 385], [$this->money($v), 130, 'right']]);
        }
        if ($groups->isEmpty()) {
            $this->note('No expenses this month.');
        }
        $this->y += 10;

        // Payroll
        $this->heading('Payroll');
        $this->row([
            ['Team member', 150, 'left', true], ['Gross', 70, 'right', true], ['Advance', 65, 'right', true],
            ['Loan', 65, 'right', true], ['Net', 85, 'right', true], ['Status', 80, 'right', true],
        ], 0.9);
        foreach ($payrolls as $p) {
            $this->row([
                [$p->employee->name ?? '-', 150], [$this->money($p->total_salary), 70, 'right'],
                [$this->money($p->advance_amount), 65, 'right'], [$this->money($p->loan_amount), 65, 'right'],
                [$this->money($p->net_salary), 85, 'right'], [$p->status, 80, 'right'],
            ]);
        }
        if ($payrolls->isEmpty()) {
            $this->note('No payroll for this month.');
        } else {
            $this->row([
                ['Total', 150, 'left', true], [$this->money($payrolls->sum('total_salary')), 70, 'right', true],
                [$this->money($payrolls->sum('advance_amount')), 65, 'right', true],
                [$this->money($payrolls->sum('loan_amount')), 65, 'right', true],
                [$this->money($payrolls->sum('net_salary')), 85, 'right', true], ['', 80],
            ], 0.93);
            $paid = $payrolls->where('status', 'Paid');
            $this->note('Paid: ' . $paid->count() . ' (net Tk ' . $this->money($paid->sum('net_salary')) . ')   Pending: '
                . ($payrolls->count() - $paid->count()));
        }
        $this->y += 10;

        // Loans
        $this->heading('Team loans (active)');
        $this->row([
            ['Team member', 150, 'left', true], ['Loan', 80, 'right', true], ['Installment', 80, 'right', true],
            ['Remaining', 90, 'right', true], ['Last month', 115, 'right', true],
        ], 0.9);
        $loans = EmployeeLoan::with('employee')->where('status', 'Active')->get();
        foreach ($loans as $loan) {
            $skipped = LoanSkip::where('employee_loan_id', $loan->id)->where('month', $month)->where('year', $year)->exists();
            $this->row([
                [($loan->employee->name ?? '-') . ($skipped ? ' (skipped)' : ''), 150], [$this->money($loan->loan_amount), 80, 'right'],
                [$this->money($loan->monthly_installment), 80, 'right'], [$this->money($loan->remaining_balance), 90, 'right'],
                [Carbon::create($loan->end_year, $loan->end_month, 1)->format('M Y'), 115, 'right'],
            ]);
        }
        if ($loans->isEmpty()) {
            $this->note('No active loans.');
        } else {
            $this->note('Total still to collect: Tk ' . $this->money($loans->sum('remaining_balance')));
        }
        $this->y += 10;

        // Bank accounts
        $this->heading('Bank and cash balances (as of ' . now()->format('d M Y') . ')');
        $this->row([['Account', 385, 'left', true], ['Balance (Tk)', 130, 'right', true]], 0.9);
        $accounts = BankAccount::orderBy('account_name')->get();
        foreach ($accounts as $a) {
            $this->row([[$a->account_name . ($a->is_active ? '' : ' (inactive)'), 385], [$this->money($a->current_balance), 130, 'right']]);
        }
        if ($accounts->isNotEmpty()) {
            $this->row([['Total', 385, 'left', true], [$this->money($accounts->sum('current_balance')), 130, 'right', true]], 0.93);
        }

        // Details
        $this->newPage();
        $this->heading('Income entries');
        $this->row([['Date', 60, 'left', true], ['Category', 120, 'left', true], ['Title', 175, 'left', true],
            ['Tk', 85, 'right', true], ['USD', 75, 'right', true]], 0.9);
        foreach ($incomes as $i) {
            $this->row([
                [Carbon::parse($i->income_date)->format('d M'), 60], [$i->incomeCategory->name ?? '-', 120],
                [$i->title ?? '', 175], [$this->money($i->amount_bdt), 85, 'right'], [$this->money($i->amount_usd), 75, 'right'],
            ]);
        }
        if ($incomes->isEmpty()) {
            $this->note('None.');
        }
        $this->y += 10;

        $this->heading('Expense entries');
        $this->row([['Date', 60, 'left', true], ['Category', 130, 'left', true], ['Title', 235, 'left', true],
            ['Tk', 90, 'right', true]], 0.9);
        foreach ($expenses as $e) {
            $this->row([
                [Carbon::parse($e->expense_date)->format('d M'), 60], [$e->expenseCategory->name ?? '-', 130],
                [$e->title ?? '', 235], [$this->money($e->amount), 90, 'right'],
            ]);
        }
        if ($expenses->isEmpty()) {
            $this->note('None.');
        }

        $this->pdf->setFooter("EcoTech OS - Accounts {$label} - Page {p} of {n}");

        return $this->pdf->output();
    }

    private function renderAllTime(): string
    {
        $this->pdf = new SimplePdf('EcoTech accounts - all time');
        $this->y   = self::TOP;

        $incomes  = Income::with('incomeCategory')->get(['id', 'income_category_id', 'income_date', 'amount_bdt', 'amount_usd']);
        $expenses = Expense::with('expenseCategory')->get(['id', 'expense_category_id', 'expense_date', 'amount']);
        $payrolls = Payroll::all();

        $incomeBdt = (float) $incomes->sum('amount_bdt');
        $incomeUsd = (float) $incomes->sum('amount_usd');
        $expenseTotal = (float) $expenses->sum('amount');

        $this->pdf->box(self::LEFT, $this->y, self::RIGHT - self::LEFT, 34, 0.12);
        $this->pdf->text(self::LEFT + 10, $this->y + 22, 'EcoTech - All-time Accounts', 16, true);
        $this->pdf->text(self::RIGHT - 10, $this->y + 22, 'Up to ' . now()->format('d M Y'), 11, true, 'right');
        $this->y += 34 + 8;
        $this->pdf->text(self::LEFT, $this->y + 8, 'Everything recorded so far. Amounts are in Taka (Tk) unless marked USD.', 8);
        $this->y += 24;

        $this->heading('Summary');
        $this->row([['Total income (Tk)', 330], [$this->money($incomeBdt), 185, 'right']]);
        $this->row([['Total income (USD)', 330], ['USD ' . $this->money($incomeUsd), 185, 'right']]);
        $this->row([['Total expenses (Tk)', 330], [$this->money($expenseTotal), 185, 'right']]);
        $this->row([['Net result (Tk)', 330, 'left', true], [$this->money($incomeBdt - $expenseTotal), 185, 'right', true]], 0.93);
        $this->y += 10;

        // Month by month
        $byMonth = [];
        foreach ($incomes as $i) {
            $k = Carbon::parse($i->income_date)->format('Y-m');
            $byMonth[$k]['in'] = ($byMonth[$k]['in'] ?? 0) + (float) $i->amount_bdt;
        }
        foreach ($expenses as $e) {
            $k = Carbon::parse($e->expense_date)->format('Y-m');
            $byMonth[$k]['out'] = ($byMonth[$k]['out'] ?? 0) + (float) $e->amount;
        }
        ksort($byMonth);

        $this->heading('Month by month');
        $this->row([['Month', 135, 'left', true], ['Income (Tk)', 130, 'right', true], ['Expenses (Tk)', 130, 'right', true], ['Net (Tk)', 120, 'right', true]], 0.9);
        $years = [];
        foreach ($byMonth as $ym => $v) {
            $in = $v['in'] ?? 0;
            $out = $v['out'] ?? 0;
            $this->row([
                [Carbon::createFromFormat('!Y-m', $ym)->format('F Y'), 135], [$this->money($in), 130, 'right'],
                [$this->money($out), 130, 'right'], [$this->money($in - $out), 120, 'right'],
            ]);
            $y = substr($ym, 0, 4);
            $years[$y]['in'] = ($years[$y]['in'] ?? 0) + $in;
            $years[$y]['out'] = ($years[$y]['out'] ?? 0) + $out;
        }
        if (!$byMonth) {
            $this->note('No income or expense entries yet.');
        }
        $this->y += 10;

        $this->heading('Year by year');
        $this->row([['Year', 135, 'left', true], ['Income (Tk)', 130, 'right', true], ['Expenses (Tk)', 130, 'right', true], ['Net (Tk)', 120, 'right', true]], 0.9);
        foreach ($years as $y => $v) {
            $this->row([[(string) $y, 135], [$this->money($v['in']), 130, 'right'], [$this->money($v['out']), 130, 'right'], [$this->money($v['in'] - $v['out']), 120, 'right']]);
        }
        $this->y += 10;

        // Categories
        $this->heading('Income by category (all time)');
        $this->row([['Category', 260, 'left', true], ['Tk', 130, 'right', true], ['USD', 125, 'right', true]], 0.9);
        $groups = $incomes->groupBy(fn ($i) => $i->incomeCategory->name ?? 'Uncategorised')
            ->map(fn ($g) => ['bdt' => (float) $g->sum('amount_bdt'), 'usd' => (float) $g->sum('amount_usd')])
            ->sortByDesc('bdt');
        foreach ($groups as $name => $v) {
            $this->row([[$name, 260], [$this->money($v['bdt']), 130, 'right'], [$this->money($v['usd']), 125, 'right']]);
        }
        $this->y += 10;

        $this->heading('Expenses by category (all time)');
        $this->row([['Category', 385, 'left', true], ['Tk', 130, 'right', true]], 0.9);
        $groups = $expenses->groupBy(fn ($e) => $e->expenseCategory->name ?? 'Uncategorised')
            ->map(fn ($g) => (float) $g->sum('amount'))->sortDesc();
        foreach ($groups as $name => $v) {
            $this->row([[$name, 385], [$this->money($v), 130, 'right']]);
        }
        $this->y += 10;

        // Payroll
        $paid = $payrolls->where('status', 'Paid');
        $this->heading('Payroll (all time)');
        $this->row([['Paid payrolls', 330], [(string) $paid->count(), 185, 'right']]);
        $this->row([['Gross salary paid', 330], [$this->money($paid->sum('total_salary')), 185, 'right']]);
        $this->row([['Advances deducted', 330], [$this->money($paid->sum('advance_amount')), 185, 'right']]);
        $this->row([['Loan installments deducted', 330], [$this->money($paid->sum('loan_amount')), 185, 'right']]);
        $this->row([['Net salary paid', 330, 'left', true], [$this->money($paid->sum('net_salary')), 185, 'right', true]], 0.93);
        $this->row([['Pending payrolls', 330], [(string) ($payrolls->count() - $paid->count()), 185, 'right']]);
        $this->y += 10;

        // Loans
        $loans = EmployeeLoan::all();
        $this->heading('Team loans (all time)');
        $this->row([['Loans given', 330], [(string) $loans->count(), 185, 'right']]);
        $this->row([['Total lent', 330], [$this->money($loans->sum('loan_amount')), 185, 'right']]);
        $this->row([['Still to collect', 330, 'left', true], [$this->money($loans->sum('remaining_balance')), 185, 'right', true]], 0.93);
        $this->y += 10;

        // Banks
        $this->heading('Bank and cash balances (as of ' . now()->format('d M Y') . ')');
        $this->row([['Account', 385, 'left', true], ['Balance (Tk)', 130, 'right', true]], 0.9);
        $accounts = BankAccount::orderBy('account_name')->get();
        foreach ($accounts as $a) {
            $this->row([[$a->account_name . ($a->is_active ? '' : ' (inactive)'), 385], [$this->money($a->current_balance), 130, 'right']]);
        }
        if ($accounts->isNotEmpty()) {
            $this->row([['Total', 385, 'left', true], [$this->money($accounts->sum('current_balance')), 130, 'right', true]], 0.93);
        }

        $this->pdf->setFooter('EcoTech OS - All-time accounts - Page {p} of {n}');

        return $this->pdf->output();
    }

    private function money($value): string
    {
        return number_format((float) $value, 2);
    }

    private function newPage(): void
    {
        $this->pdf->addPage();
        $this->y = self::TOP;
    }

    private function space(float $height): void
    {
        if ($this->y + $height > self::LIMIT) {
            $this->newPage();
        }
    }

    private function heading(string $text): void
    {
        $this->space(60);
        $this->pdf->text(self::LEFT, $this->y + 12, $text, 12, true);
        $this->y += 18;
    }

    private function note(string $text): void
    {
        $this->space(16);
        $this->pdf->text(self::LEFT + 4, $this->y + 10, $text, 9);
        $this->y += 16;
    }

    /**
     * One table row. Each cell: [text, width, align = left, bold = false].
     */
    private function row(array $cells, ?float $shade = null): void
    {
        $this->space(18);

        if ($shade !== null) {
            $this->pdf->box(self::LEFT, $this->y, self::RIGHT - self::LEFT, 16, $shade);
        }

        $x = self::LEFT;

        foreach ($cells as $cell) {
            [$text, $width] = $cell;
            $align = $cell[2] ?? 'left';
            $bold  = $cell[3] ?? false;

            $text = $this->pdf->fit((string) $text, $width - 8, 9, $bold);
            $tx   = $align === 'right' ? $x + $width - 4 : $x + 4;

            $this->pdf->text($tx, $this->y + 11, $text, 9, $bold, $align);
            $x += $width;
        }

        $this->pdf->line(self::LEFT, $this->y + 16, self::RIGHT, $this->y + 16, 0.3, 0.8);
        $this->y += 16;
    }
}
