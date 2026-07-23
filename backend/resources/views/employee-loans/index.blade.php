@extends('layouts.app')

@section('title', 'Employee Loans')

@section('page_title', 'Employee Loans')

@section('content')

<div class="max-w-7xl mx-auto">

    @if(session('success'))
        <div class="mb-5 rounded-lg bg-green-100 border border-green-300 text-green-700 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow">

        <div class="flex items-center justify-between border-b px-6 py-4">

            <div>

                <h2 class="text-xl font-bold">
                    Employee Loans
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Manage employee loan records.
                </p>

            </div>

            <a href="{{ route('employee-loans.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">

                + Add Loan

            </a>

        </div>

        <div class="p-6">

            <form method="GET" class="mb-6">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search employee..."
                    class="w-full rounded-lg border-gray-300">

            </form>

            <div class="overflow-x-auto">

                <table class="min-w-full border">

                    <thead class="bg-gray-100">

                        <tr>

                            <th class="px-4 py-3 border text-left">Employee</th>

                            <th class="px-4 py-3 border text-right">Loan</th>

                            <th class="px-4 py-3 border text-right">Installment</th>

                            <th class="px-4 py-3 border text-right">Remaining</th>

                            <th class="px-4 py-3 border text-center">Status</th>

                            <th class="px-4 py-3 border text-center">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($loans as $loan)

                        <tr class="hover:bg-gray-50">

                            <td class="border px-4 py-3">

                                <div class="font-semibold">

                                    {{ $loan->employee->name }}

                                </div>

                                <div class="text-xs text-gray-500">

                                    {{ $loan->employee->employee_id }}

                                </div>

                            </td>

                            <td class="border px-4 py-3 text-right">

                                {{ number_format($loan->loan_amount,2) }}

                            </td>

                            <td class="border px-4 py-3 text-right">

                                {{ number_format($loan->monthly_installment,2) }}

                            </td>

                            <td class="border px-4 py-3 text-right font-semibold">

                                {{ number_format($loan->remaining_balance,2) }}

                            </td>

                            <td class="border px-4 py-3 text-center">

                                @if($loan->status=='Active')

                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">

                                        Active

                                    </span>

                                @else

                                    <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-xs">

                                        Closed

                                    </span>

                                @endif

                            </td>

                            <td class="border px-4 py-3">

                                <div class="flex justify-center gap-2">

                                    <a href="{{ route('employee-loans.show',$loan) }}"
                                       class="px-3 py-1 rounded bg-sky-500 text-white">

                                        View

                                    </a>

                                    <a href="{{ route('employee-loans.edit',$loan) }}"
                                       class="px-3 py-1 rounded bg-yellow-500 text-white">

                                        Edit

                                    </a>

                                    <form
                                        action="{{ route('employee-loans.destroy',$loan) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this loan?')">

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            class="px-3 py-1 rounded bg-red-600 text-white">

                                            Delete

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center py-8 text-gray-500">

                                No employee loans found.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-6">

                {{ $loans->links() }}

            </div>

        </div>

    </div>

</div>

@endsection