<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return view('portal.salary.index', [
                'employee' => null,
                'latestPayroll' => null,
                'payrolls' => collect(),
            ]);
        }

        $latestPayroll = $employee->payrolls()
            ->latest('year')
            ->latest('month')
            ->first();

        $payrolls = $employee->payrolls()
            ->latest('year')
            ->latest('month')
            ->paginate(10);

        return view('portal.salary.index', compact(
            'employee',
            'latestPayroll',
            'payrolls'
        ));
    }
}