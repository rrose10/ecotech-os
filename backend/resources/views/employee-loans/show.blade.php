@extends('layouts.app')

@section('title', 'Team Loan Details')

@section('page_title', 'Team Loan Details')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="bg-white rounded-xl shadow">

        <div class="flex justify-between items-center border-b px-6 py-4">

            <div>

                <h2 class="text-xl font-bold">
                    Team Loan Details
                </h2>

                <p class="text-sm text-gray-500">
                    View complete employee loan information.
                </p>

            </div>

            <a href="{{ route('employee-loans.index') }}"
               class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg">

                ← Back

            </a>

        </div>

        <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="text-gray-500 text-sm">Team Member</label>
                    <div class="font-semibold text-lg">
                        {{ $employeeLoan->employee->name }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $employeeLoan->employee->employee_id }}
                    </div>
                </div>

                <div>
                    <label class="text-gray-500 text-sm">Bank Account</label>
                    <div class="font-semibold">
                        {{ $employeeLoan->bankAccount->account_name }}
                    </div>
                </div>

                <div>
                    <label class="text-gray-500 text-sm">Loan Amount</label>
                    <div class="font-semibold text-blue-600">
                        ৳ {{ number_format($employeeLoan->loan_amount,2) }}
                    </div>
                </div>

                <div>
                    <label class="text-gray-500 text-sm">Monthly Installment</label>
                    <div class="font-semibold">
                        ৳ {{ number_format($employeeLoan->monthly_installment,2) }}
                    </div>
                </div>

                <div>
                    <label class="text-gray-500 text-sm">Remaining Balance</label>
                    <div class="font-semibold text-red-600">
                        ৳ {{ number_format($employeeLoan->remaining_balance,2) }}
                    </div>
                </div>

                <div>
                    <label class="text-gray-500 text-sm">Loan Date</label>
                    <div>
                        {{ $employeeLoan->loan_date->format('d M Y') }}
                    </div>
                </div>

                <div>
                    <label class="text-gray-500 text-sm">Deduction Starts</label>
                    <div>
                        {{ DateTime::createFromFormat('!m',$employeeLoan->start_month)->format('F') }}
                        {{ $employeeLoan->start_year }}
                    </div>
                </div>

                <div>
                    <label class="text-gray-500 text-sm">Status</label>

                    <div>

                        @if($employeeLoan->status=='Active')

                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full">

                                Active

                            </span>

                        @else

                            <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full">

                                Closed

                            </span>

                        @endif

                    </div>

                </div>

            </div>

            <div class="mt-8">

                <label class="text-gray-500 text-sm">

                    Remarks

                </label>

                <div class="mt-2 rounded-lg border p-4 bg-gray-50">

                    {{ $employeeLoan->remarks ?: 'No remarks available.' }}

                </div>

            </div>

            <div class="mt-8 flex gap-3">

                <a href="{{ route('employee-loans.edit',$employeeLoan) }}"
                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2 rounded-lg">

                    Edit

                </a>

                <a href="{{ route('employee-loans.index') }}"
                   class="bg-gray-700 hover:bg-gray-800 text-white px-5 py-2 rounded-lg">

                    Back

                </a>

            </div>

        </div>

    </div>

</div>

@endsection