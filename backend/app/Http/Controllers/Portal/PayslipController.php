<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    /**
     * Printable payslip. The employee can only open their own;
     * "Print / Save as PDF" is handled by the browser.
     */
    public function show(Payroll $payroll)
    {
        $employee = Auth::user()->employee;

        abort_unless($employee && $payroll->employee_id === $employee->id, 404);

        $payroll->load('employee.department');

        $company = [
            'name' => Setting::get('company_name', 'EcoTech'),
            'email' => Setting::get('company_email', ''),
            'phone' => Setting::get('company_phone', ''),
            'address' => Setting::get('company_address', ''),
        ];

        return view('portal.salary.payslip', compact('payroll', 'company'));
    }
}
