@extends('layouts.app')

@section('title', 'Expenses')
@section('page_title', 'Expenses')

@section('content')

<div class="p-8">

    <div class="flex justify-between items-center mb-6">

        <h1 class="text-3xl font-bold">
            Expenses
        </h1>

        <a href="{{ route('expenses.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Add Expense
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
                placeholder="Search Expense..."
                class="w-full md:w-80 border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">
        </form>

        @if($expenses->count())

            <table class="w-full">

                <thead>
                    <tr class="border-b">
                        <th class="text-left p-2">Category</th>
                        <th class="text-left p-2">Title</th>
                        <th class="text-left p-2">Amount</th>
                        <th class="text-left p-2">Payment</th>
                        <th class="text-left p-2">Date</th>
                        <th class="text-left p-2">Created By</th>
                        <th class="text-left p-2">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($expenses as $expense)

                        <tr class="border-b">

                            <td class="p-2">
                                {{ $expense->expenseCategory->name }}
                            </td>

                            <td class="p-2">
                                {{ $expense->title }}
                            </td>

                            <td class="p-2">
                                ৳ {{ number_format($expense->amount, 2) }}
                            </td>

                            <td class="p-2">
                                {{ $expense->payment_method }}
                            </td>

                            <td class="p-2">
                                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}
                            </td>

                            <td class="p-2">
                                {{ $expense->creator->name }}
                            </td>

                            <td class="p-2 flex gap-2">

                                <a href="{{ route('expenses.edit', $expense) }}"
                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                    Edit
                                </a>

                                <form action="{{ route('expenses.destroy', $expense) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this expense?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

            <div class="mt-6">
                {{ $expenses->links() }}
            </div>

        @else

            <p class="text-gray-500">
                No Expenses Found.
            </p>

        @endif

    </div>

</div>

@endsection