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

            {{-- Skip a month --}}
            <div class="mt-8 border-t pt-6">

                <h3 class="text-lg font-bold mb-1">Skip a month</h3>
                <p class="text-sm text-gray-500 mb-4">
                    No installment is collected in the chosen month. The last installment moves one month later
                    (now {{ DateTime::createFromFormat('!m', $employeeLoan->end_month)->format('F') }} {{ $employeeLoan->end_year }}).
                </p>

                @if($errors->any())
                    <div class="mb-4 rounded-lg bg-red-100 border border-red-300 text-red-700 px-4 py-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-4 rounded-lg bg-green-100 border border-green-300 text-green-700 px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif

                @if($employeeLoan->status === 'Active')
                <form method="POST" action="{{ route('employee-loans.skip', $employeeLoan) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    @csrf

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Month</label>
                        <select name="month" class="w-full rounded-lg border-gray-300">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == now()->month)>
                                    {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Year</label>
                        <input type="number" name="year" value="{{ now()->year }}" class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Reason (optional)</label>
                        <input type="text" name="reason" maxlength="255" class="w-full rounded-lg border-gray-300">
                    </div>

                    <button class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-lg">
                        Skip this month
                    </button>
                </form>
                @endif

                @if($employeeLoan->skips->isNotEmpty())
                    <table class="min-w-full border mt-6 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border text-left">Skipped month</th>
                                <th class="px-4 py-2 border text-left">Reason</th>
                                <th class="px-4 py-2 border"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employeeLoan->skips as $skip)
                                <tr>
                                    <td class="px-4 py-2 border">
                                        {{ DateTime::createFromFormat('!m', $skip->month)->format('F') }} {{ $skip->year }}
                                    </td>
                                    <td class="px-4 py-2 border">{{ $skip->reason ?? '--' }}</td>
                                    <td class="px-4 py-2 border text-center">
                                        <form method="POST" action="{{ route('employee-loans.skip.undo', [$employeeLoan, $skip]) }}"
                                              onsubmit="return confirm('Collect this installment again?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-3 py-1 rounded bg-gray-600 text-white">Undo</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

            </div>

        </div>

    </div>

</div>

@endsection