@extends('layouts.app')

@section('title', 'Incomes')
@section('page_title', 'Incomes')

@section('content')

<div class="p-8">

    <div class="flex justify-between items-center mb-6">

        <h1 class="text-3xl font-bold">
            Incomes
        </h1>

        <a href="{{ route('incomes.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Add Income
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
                placeholder="Search Income..."
                class="w-full md:w-80 border rounded px-4 py-2 focus:outline-none focus:ring focus:ring-blue-200">

        </form>

        @if($incomes->count())

            <table class="w-full">

                <thead>

                    <tr class="border-b">

                        <th class="text-left p-2">Category</th>
                        <th class="text-left p-2">Title</th>
                        <th class="text-left p-2">BDT</th>
                        <th class="text-left p-2">USD</th>
                        <th class="text-left p-2">Date</th>
                        <th class="text-left p-2">Payment</th>
                        <th class="text-left p-2">Created By</th>
                        <th class="text-left p-2">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($incomes as $income)

                        <tr class="border-b hover:bg-gray-50">

                            <td class="p-2">
                                {{ $income->incomeCategory->name }}
                            </td>

                            <td class="p-2 font-medium">
                                {{ $income->title }}
                            </td>

                            <td class="p-2">
                                ৳{{ number_format($income->amount_bdt, 2) }}
                            </td>

                            <td class="p-2">
                                ${{ number_format($income->amount_usd, 2) }}
                            </td>

                            <td class="p-2">
                                {{ \Carbon\Carbon::parse($income->income_date)->format('d M Y') }}
                            </td>

                            <td class="p-2">
                                {{ $income->payment_method }}
                            </td>

                            <td class="p-2">
                                {{ $income->creator->name }}
                            </td>

                            <td class="p-2">

                                <div class="flex gap-2">

                                    <a href="{{ route('incomes.edit', $income) }}"
                                       class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                        Edit
                                    </a>

                                    <form action="{{ route('incomes.destroy', $income) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete this income?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

            <div class="mt-6">
                {{ $incomes->links() }}
            </div>

        @else

            <p class="text-gray-500">
                No Income Found.
            </p>

        @endif

    </div>

</div>

@endsection