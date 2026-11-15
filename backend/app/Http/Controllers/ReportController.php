<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function finance(Request $request)
    {
        [$month, $year] = $this->period($request);

        $incomes = Income::with('incomeCategory')
            ->whereYear('income_date', $year)->whereMonth('income_date', $month)->get();
        $expenses = Expense::with('expenseCategory')
            ->whereYear('expense_date', $year)->whereMonth('expense_date', $month)->get();

        $incomeBdt = (float) $incomes->sum('amount_bdt');
        $incomeUsd = (float) $incomes->sum('amount_usd');
        $expenseTotal = (float) $expenses->sum('amount');

        $incomeByCategory = $incomes->groupBy(fn ($i) => $i->incomeCategory->name ?? 'Uncategorised')
            ->map(fn ($g) => ['bdt' => (float) $g->sum('amount_bdt'), 'usd' => (float) $g->sum('amount_usd')])
            ->sortByDesc('bdt');
        $expenseByCategory = $expenses->groupBy(fn ($e) => $e->expenseCategory->name ?? 'Uncategorised')
            ->map(fn ($g) => (float) $g->sum('amount'))
            ->sortDesc();

        if ($request->query('export') === 'csv') {
            $rows = [['Type', 'Category', 'BDT', 'USD']];
            foreach ($incomeByCategory as $name => $v) {
                $rows[] = ['Income', $name, $v['bdt'], $v['usd']];
            }
            foreach ($expenseByCategory as $name => $v) {
                $rows[] = ['Expense', $name, $v, ''];
            }
            $rows[] = ['Net (BDT)', '', $incomeBdt - $expenseTotal, ''];

            return $this->csv("finance-{$year}-{$month}.csv", $rows);
        }

        return view('reports.finance', compact(
            'month', 'year', 'incomeBdt', 'incomeUsd', 'expenseTotal',
            'incomeByCategory', 'expenseByCategory'
        ));
    }

    public function attendance(Request $request)
    {
        [$month, $year] = $this->period($request);

        $records = Attendance::whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get()
            ->groupBy('employee_id');

        $rows = Employee::orderBy('name')->get()->map(function ($employee) use ($records) {
            $r = $records->get($employee->id, collect());

            return [
                'employee' => $employee,
                'present' => $r->where('status', 'Present')->count(),
                'late' => $r->where('status', 'Late')->count(),
                'absent' => $r->where('status', 'Absent')->count(),
                'leave' => $r->where('status', 'Leave')->count(),
                'hours' => round($r->sum('working_minutes') / 60, 1),
            ];
        });

        if ($request->query('export') === 'csv') {
            $csv = [['Employee ID', 'Name', 'Present', 'Late', 'Absent', 'Leave', 'Working hours']];
            foreach ($rows as $row) {
                $csv[] = [$row['employee']->employee_id, $row['employee']->name, $row['present'],
                    $row['late'], $row['absent'], $row['leave'], $row['hours']];
            }

            return $this->csv("attendance-{$year}-{$month}.csv", $csv);
        }

        return view('reports.attendance', compact('month', 'year', 'rows'));
    }

    public function payroll(Request $request)
    {
        [$month, $year] = $this->period($request);

        $payrolls = Payroll::with('employee')
            ->where('year', $year)->where('month', $month)
            ->orderBy('id')->get();

        $totals = [
            'gross' => (float) $payrolls->sum('total_salary'),
            'advance' => (float) $payrolls->sum('advance_amount'),
            'loan' => (float) $payrolls->sum('loan_amount'),
            'net' => (float) $payrolls->sum('net_salary'),
            'paid' => (float) $payrolls->where('status', 'Paid')->sum('net_salary'),
            'pending' => (float) $payrolls->where('status', 'Pending')->sum('net_salary'),
        ];

        if ($request->query('export') === 'csv') {
            $csv = [['Employee ID', 'Name', 'Gross', 'Advance', 'Loan', 'Net', 'Status']];
            foreach ($payrolls as $p) {
                $csv[] = [$p->employee->employee_id ?? '', $p->employee->name ?? '', $p->total_salary,
                    $p->advance_amount, $p->loan_amount, $p->net_salary, $p->status];
            }

            return $this->csv("payroll-{$year}-{$month}.csv", $csv);
        }

        return view('reports.payroll', compact('month', 'year', 'payrolls', 'totals'));
    }

    private function period(Request $request): array
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        return [min(max($month, 1), 12), min(max($year, 2000), 2100)];
    }

    private function csv(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Bangla names correctly
            foreach ($rows as $row) {
                // Stop spreadsheet formula injection from user-entered text
                $row = array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'".$v : $v, $row);
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
