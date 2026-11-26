@extends('layouts.app')

@section('title', 'Transfers')

@section('page_title', 'Transfers')

@section('content')

<div class="max-w-7xl mx-auto">

    @if(session('success'))
        <div class="mb-5 rounded-lg bg-green-100 border border-green-300 text-green-700 px-4 py-3">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-5 rounded-lg bg-red-100 border border-red-300 text-red-700 px-4 py-3">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow">

        <div class="flex items-center justify-between border-b px-6 py-4">
            <div>
                <h2 class="text-xl font-bold">Transfers</h2>
                <p class="text-sm text-gray-500 mt-1">Money moved between your own accounts (not income or expense).</p>
            </div>
            <a href="{{ route('fund-transfers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">+ New transfer</a>
        </div>

        <div class="p-6">

            <form method="GET" class="mb-6 flex flex-wrap items-center gap-2">
                <select name="account" class="border rounded p-2" onchange="this.form.submit()">
                    <option value="">All accounts</option>
                    @foreach($accounts as $a)
                        <option value="{{ $a->id }}" @selected(request('account') == $a->id)>{{ $a->account_name }}</option>
                    @endforeach
                </select>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 border text-left">Date</th>
                            <th class="px-4 py-3 border text-left">From</th>
                            <th class="px-4 py-3 border text-left">To</th>
                            <th class="px-4 py-3 border text-right">Amount</th>
                            <th class="px-4 py-3 border text-left">Note</th>
                            <th class="px-4 py-3 border text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($transfers as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-3">{{ $t->transfer_date->format('d M Y') }}</td>
                            <td class="border px-4 py-3">{{ $t->fromAccount->account_name ?? '-' }}</td>
                            <td class="border px-4 py-3">{{ $t->toAccount->account_name ?? '-' }}</td>
                            <td class="border px-4 py-3 text-right font-semibold">{{ number_format($t->amount, 2) }}</td>
                            <td class="border px-4 py-3">{{ $t->remarks ?? '--' }}</td>
                            <td class="border px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('fund-transfers.edit', $t) }}" class="px-3 py-1 rounded bg-yellow-500 text-white">Edit</a>
                                    <form action="{{ route('fund-transfers.destroy', $t) }}" method="POST"
                                          onsubmit="return confirm('Delete this transfer? The money goes back to the first account.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-1 rounded bg-red-600 text-white">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-gray-500">No transfers yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $transfers->links() }}</div>

        </div>
    </div>
</div>

@endsection
