@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto">

    <div class="flex items-center justify-between mb-6">

        <div>
            <h1 class="text-3xl font-bold">
                Advance Salaries
            </h1>

            <p class="text-gray-500 mt-1">
                Manage team advance salaries.
            </p>
        </div>

        <a href="{{ route('advance-salaries.create') }}"
           class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-white font-medium hover:bg-blue-700">

            + Add Advance Salary

        </a>

    </div>

    {{-- Success --}}
    @if(session('success'))
        <div class="mb-5 rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Search --}}
    <div class="bg-white rounded-lg shadow border mb-6">

        <div class="p-4">

            <form method="GET">

                <div class="flex gap-3">

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search employee..."
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                    <button
                        class="rounded-lg bg-gray-800 px-5 text-white hover:bg-black">

                        Search

                    </button>

                </div>

            </form>

        </div>

    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow border">

        <table class="min-w-full">

            <thead class="bg-gray-100">

                <tr>

                    <th class="px-4 py-3 text-left">
                        Team Member
                    </th>

                    <th class="px-4 py-3 text-left">
                        Amount
                    </th>

                    <th class="px-4 py-3 text-left">
                        Advance Date
                    </th>

                    <th class="px-4 py-3 text-left">
                        Deduction
                    </th>

                    <th class="px-4 py-3 text-left">
                        Status
                    </th>

                    <th class="px-4 py-3 text-center">
                        Action
                    </th>

                </tr>

            </thead>

            <tbody>

            @forelse($advanceSalaries as $advance)

                <tr class="border-t">

                    <td class="px-4 py-3">

                        <div class="font-medium">
                            {{ $advance->employee->name }}
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ $advance->employee->employee_id }}
                        </div>

                    </td>

                    <td class="px-4 py-3 font-semibold">
                        ৳ {{ number_format($advance->amount,2) }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $advance->advance_date->format('d M Y') }}
                    </td>

                    <td class="px-4 py-3">
                        {{ date('F', mktime(0,0,0,$advance->deduction_month,1)) }}
                        {{ $advance->deduction_year }}
                    </td>

                    <td class="px-4 py-3">

                        @if($advance->status=='Pending')

                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-sm text-yellow-700">
                                Pending
                            </span>

                        @elseif($advance->status=='Deducted')

                            <span class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700">
                                Deducted
                            </span>

                        @else

                            <span class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700">
                                Cancelled
                            </span>

                        @endif

                    </td>

                    <td class="px-4 py-3 text-center">

                        <a href="{{ route('advance-salaries.show',$advance) }}"
                           class="text-blue-600 hover:underline">

                            View

                        </a>

                        |

                        <a href="{{ route('advance-salaries.edit',$advance) }}"
                           class="text-indigo-600 hover:underline">

                            Edit

                        </a>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6"
                        class="px-4 py-8 text-center text-gray-500">

                        No advance salary found.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-6">

        {{ $advanceSalaries->links() }}

    </div>

</div>

@endsection