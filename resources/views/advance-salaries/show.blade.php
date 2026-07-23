@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="flex items-center justify-between mb-8">

        <div>
            <h1 class="text-3xl font-bold">
                Advance Salary Details
            </h1>

            <p class="text-gray-500 mt-1">
                View advance salary information.
            </p>
        </div>

        <a
            href="{{ route('advance-salaries.index') }}"
            class="rounded-lg border px-5 py-2 hover:bg-gray-100">

            ← Back

        </a>

    </div>

    <div class="bg-white rounded-xl shadow border overflow-hidden">

        <table class="min-w-full">

            <tbody>

                <tr class="border-b">
                    <td class="px-6 py-4 font-semibold w-64">Employee</td>
                    <td class="px-6 py-4">
                        {{ $advanceSalary->employee->employee_id }}
                        -
                        {{ $advanceSalary->employee->name }}
                    </td>
                </tr>

                <tr class="border-b">
                    <td class="px-6 py-4 font-semibold">Payment Account</td>
                    <td class="px-6 py-4">
                        {{ $advanceSalary->bankAccount->account_name }}
                    </td>
                </tr>

                <tr class="border-b">
                    <td class="px-6 py-4 font-semibold">Advance Amount</td>
                    <td class="px-6 py-4 font-bold">
                        ৳ {{ number_format($advanceSalary->amount,2) }}
                    </td>
                </tr>

                <tr class="border-b">
                    <td class="px-6 py-4 font-semibold">Advance Date</td>
                    <td class="px-6 py-4">
                        {{ $advanceSalary->advance_date->format('d M Y') }}
                    </td>
                </tr>

                <tr class="border-b">
                    <td class="px-6 py-4 font-semibold">Deduction Month</td>
                    <td class="px-6 py-4">
                        {{ date('F', mktime(0,0,0,$advanceSalary->deduction_month,1)) }}
                        {{ $advanceSalary->deduction_year }}
                    </td>
                </tr>

                <tr class="border-b">
                    <td class="px-6 py-4 font-semibold">Status</td>
                    <td class="px-6 py-4">

                        @if($advanceSalary->status=='Pending')

                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm text-yellow-700">
                                Pending
                            </span>

                        @elseif($advanceSalary->status=='Deducted')

                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">
                                Deducted
                            </span>

                        @else

                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700">
                                Cancelled
                            </span>

                        @endif

                    </td>
                </tr>

                <tr>
                    <td class="px-6 py-4 font-semibold">Remarks</td>
                    <td class="px-6 py-4">
                        {{ $advanceSalary->remarks ?: '-' }}
                    </td>
                </tr>

            </tbody>

        </table>

    </div>

    <div class="mt-6 flex gap-3">

        <a
            href="{{ route('advance-salaries.edit', $advanceSalary) }}"
            class="rounded-lg bg-indigo-600 px-5 py-3 text-white hover:bg-indigo-700">

            Edit

        </a>

        <form
            action="{{ route('advance-salaries.destroy', $advanceSalary) }}"
            method="POST"
            onsubmit="return confirm('Are you sure you want to delete this advance salary?')">

            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="rounded-lg bg-red-600 px-5 py-3 text-white hover:bg-red-700">

                Delete

            </button>

        </form>

    </div>

</div>

@endsection