@extends('layouts.app')

@section('title', 'Payrolls')
@section('page_title', 'Payrolls')

@section('content')

<div class="p-8">

    <div class="flex justify-between items-center mb-6">

        <h1 class="text-3xl font-bold">
            Payrolls
        </h1>

        <a href="{{ route('payrolls.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Add Payroll
        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">

        <form method="GET" class="mb-4">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search team member..."
                class="w-full md:w-80 border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">
        </form>

        @if($payrolls->count())

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left p-3">Team Member</th>
                            <th class="text-left p-3">Month</th>
                            <th class="text-left p-3">Year</th>
                            <th class="text-left p-3">Basic Salary</th>
                            <th class="text-left p-3">Bonus</th>
                            <th class="text-left p-3">Total Salary</th>
                            <th class="text-left p-3">Status</th>
                            <th class="text-left p-3">Payment Date</th>
                            <th class="text-center p-3">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($payrolls as $payroll)

                            <tr class="border-b hover:bg-gray-50">

                                <td class="p-3">
                                    <div class="font-semibold">
                                        {{ $payroll->employee->name }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $payroll->employee->employee_id }}
                                    </div>
                                </td>

                                <td class="p-3">
                                    {{ date('F', mktime(0,0,0,$payroll->month,1)) }}
                                </td>

                                <td class="p-3">
                                    {{ $payroll->year }}
                                </td>

                                <td class="p-3">
                                    ৳ {{ number_format($payroll->basic_salary,2) }}
                                </td>

                                <td class="p-3">
                                    ৳ {{ number_format($payroll->bonus,2) }}
                                </td>

                                <td class="p-3 font-bold text-green-700">
                                    ৳ {{ number_format($payroll->total_salary,2) }}
                                </td>

                                <td class="p-3">
                                    @if($payroll->status == 'Paid')
                                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">
                                            Paid
                                        </span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td class="p-3">
                                    {{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : '-' }}
                                </td>

                                <td class="p-3">

                                    <div class="flex items-center justify-center gap-2">

                                        <a href="{{ route('payrolls.show', $payroll) }}"
                                           class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 text-sm">
                                            View
                                        </a>

                                        <a href="{{ route('payrolls.edit', $payroll) }}"
                                           class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('payrolls.destroy', $payroll) }}"
                                              method="POST"
                                              onsubmit="return confirm('Delete this payroll?')">

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="mt-6">
                {{ $payrolls->links() }}
            </div>

        @else

            <div class="text-center py-10">

                <p class="text-gray-500 text-lg">
                    No Payroll Found.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection