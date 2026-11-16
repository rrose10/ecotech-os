@extends('layouts.app')

@section('title', 'Payroll Details')
@section('page_title', 'Payroll Details')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="flex justify-between items-center mb-6 no-print">

        <h1 class="text-3xl font-bold">
            Salary Slip
        </h1>

        <div class="flex gap-2">

            <a href="{{ route('payrolls.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                ← Back
            </a>

            <button
                onclick="window.print()"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                🖨 Print
            </button>

        </div>

    </div>

    <div class="bg-white shadow-lg rounded-lg p-8">

        {{-- Company --}}
        <div class="text-center border-b pb-6 mb-8">

            <h2 class="text-3xl font-bold text-slate-800">
                EcoTech
            </h2>

            <p class="text-gray-500">
                Office Management System
            </p>

            <h3 class="text-xl font-semibold mt-4">
                Salary Slip
            </h3>

        </div>

        {{-- Team Member Information --}}
        <div class="mb-8">

            <h4 class="text-lg font-semibold border-b pb-2 mb-4">
                Team Member Information
            </h4>

            <div class="grid grid-cols-2 gap-4">

                <div>
                    <strong>Member ID:</strong><br>
                    {{ $payroll->employee->employee_id }}
                </div>

                <div>
                    <strong>Team Member Name:</strong><br>
                    {{ $payroll->employee->name }}
                </div>

                <div>
                    <strong>Department:</strong><br>
                    {{ $payroll->employee->department->name ?? 'N/A' }}
                </div>

                <div>
                    <strong>Status:</strong><br>

                    @if($payroll->status == 'Paid')

                        <span class="text-green-600 font-semibold">
                            Paid
                        </span>

                    @else

                        <span class="text-yellow-600 font-semibold">
                            Pending
                        </span>

                    @endif

                </div>

            </div>

        </div>

        {{-- Payroll Information --}}
        <div class="mb-8">

            <h4 class="text-lg font-semibold border-b pb-2 mb-4">
                Payroll Information
            </h4>

            <table class="w-full border">

                <tbody>

                    <tr class="border-b">
                        <td class="p-3 font-medium">Month</td>
                        <td class="p-3">
                            {{ date('F', mktime(0,0,0,$payroll->month,1)) }}
                        </td>
                    </tr>

                    <tr class="border-b">
                        <td class="p-3 font-medium">Year</td>
                        <td class="p-3">
                            {{ $payroll->year }}
                        </td>
                    </tr>

                    <tr class="border-b">
                        <td class="p-3 font-medium">Basic Salary</td>
                        <td class="p-3">
                            ৳ {{ number_format($payroll->basic_salary,2) }}
                        </td>
                    </tr>

                    <tr class="border-b">
                        <td class="p-3 font-medium">Bonus</td>
                        <td class="p-3">
                            ৳ {{ number_format($payroll->bonus,2) }}
                        </td>
                    </tr>

                    <tr class="border-b eco-row-gross">
                        <td class="p-3 font-bold">
                            Total Salary
                        </td>

                        <td class="p-3 font-bold">
                            ৳ {{ number_format($payroll->total_salary,2) }}
                        </td>
                    </tr>

                    <tr class="border-b">
                        <td class="p-3 font-medium">Advance Salary Deducted</td>
                        <td class="p-3 {{ $payroll->advance_amount > 0 ? 'eco-minus' : '' }}">
                            {{ $payroll->advance_amount > 0 ? '− ' : '' }}৳ {{ number_format($payroll->advance_amount,2) }}
                        </td>
                    </tr>

                    <tr class="border-b">
                        <td class="p-3 font-medium">Loan Installment Deducted</td>
                        <td class="p-3 {{ $payroll->loan_amount > 0 ? 'eco-minus' : '' }}">
                            {{ $payroll->loan_amount > 0 ? '− ' : '' }}৳ {{ number_format($payroll->loan_amount,2) }}
                        </td>
                    </tr>

                    <tr class="border-b eco-row-net">
                        <td class="p-3 font-bold">
                            Net Salary
                        </td>

                        <td class="p-3 font-bold">
                            ৳ {{ number_format($payroll->net_salary,2) }}
                        </td>
                    </tr>

                    <tr class="border-b">
                        <td class="p-3 font-medium">
                            Payment Date
                        </td>

                        <td class="p-3">
                            {{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : '-' }}
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

        {{-- Remarks --}}
        <div class="mb-8">

            <h4 class="text-lg font-semibold border-b pb-2 mb-4">
                Remarks
            </h4>

            <p class="text-gray-700">
                {{ $payroll->remarks ?: 'No remarks available.' }}
            </p>

        </div>

        {{-- Footer --}}
        <div class="border-t pt-8 grid grid-cols-2 gap-8">

            <div>

                <p class="font-medium mb-12">
                    ______________________
                </p>

                <p>
                    Team Member Signature
                </p>

            </div>

            <div class="text-right">

                <p class="font-medium mb-12">
                    ______________________
                </p>

                <p>
                    Authorized Signature
                </p>

            </div>

        </div>

    </div>

</div>

@endsection