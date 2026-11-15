@extends('layouts.app')
@section('title', $item->name)
@section('content')
<div class="p-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold">{{ $item->name }}</h1>
            <p class="text-sm text-gray-500">SKU {{ $item->sku }} · Reorder at {{ $item->reorder_level }} {{ $item->unit }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory-items.edit', $item) }}" class="bg-blue-600 text-white px-4 py-2 rounded">Edit</a>
            <form action="{{ route('inventory-items.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this item and its history?')">
                @csrf @method('DELETE')
                <button class="bg-red-600 text-white px-4 py-2 rounded">Delete</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">In stock</p>
            <p class="text-4xl font-bold {{ $item->isLow() ? 'text-red-600' : 'text-green-600' }}">{{ $item->quantity }} <span class="text-lg">{{ $item->unit }}</span></p>
            @if($item->isLow())<p class="mt-1 text-sm text-red-600">Low stock, time to reorder.</p>@endif

            <form action="{{ route('inventory-items.move', $item) }}" method="POST" class="mt-6 space-y-3">
                @csrf
                <select name="type" class="w-full border rounded p-3">
                    <option value="In">Stock in (received)</option>
                    <option value="Out">Stock out (used / given)</option>
                </select>
                <input type="number" min="1" name="quantity" placeholder="Quantity" class="w-full border rounded p-3" required>
                <input type="text" name="note" maxlength="255" placeholder="Note (optional)" class="w-full border rounded p-3">
                @error('quantity')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded">Save movement</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto lg:col-span-2">
            <h2 class="text-xl font-bold mb-3">History</h2>
            @if($movements->count())
            <table class="w-full">
                <thead><tr class="border-b">
                    <th class="text-left p-2">Date</th><th class="text-left p-2">Type</th>
                    <th class="text-left p-2">Qty</th><th class="text-left p-2">Balance</th>
                    <th class="text-left p-2">Note</th><th class="text-left p-2">By</th>
                </tr></thead>
                <tbody>
                @foreach($movements as $m)
                    <tr class="border-b">
                        <td class="p-2">{{ $m->created_at->format('d M Y H:i') }}</td>
                        <td class="p-2">{{ $m->type }}</td>
                        <td class="p-2">{{ $m->type === 'In' ? '+' : '−' }}{{ $m->quantity }}</td>
                        <td class="p-2">{{ $m->balance_after }}</td>
                        <td class="p-2">{{ $m->note ?? '—' }}</td>
                        <td class="p-2">{{ $m->creator->name ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $movements->links() }}</div>
            @else
                <p class="text-gray-500">No stock movements yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
