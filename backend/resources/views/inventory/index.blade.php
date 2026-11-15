@extends('layouts.app')
@section('title', 'Inventory')
@section('content')
<div class="p-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold">Inventory</h1>
            <p class="text-sm text-gray-500">{{ $lowCount }} {{ \Illuminate\Support\Str::plural('item', $lowCount) }} at or below reorder level</p>
        </div>
        <a href="{{ route('inventory-items.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Add Item</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or SKU" class="border rounded p-2 w-64">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="low" value="1" @checked(request()->boolean('low'))> Low stock only</label>
        <button class="bg-green-600 text-white px-4 py-2 rounded">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
        @if($items->count())
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">SKU</th>
                    <th class="text-left p-2">Item</th>
                    <th class="text-left p-2">In stock</th>
                    <th class="text-left p-2">Reorder at</th>
                    <th class="text-left p-2">Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr class="border-b">
                    <td class="p-2">{{ $item->sku }}</td>
                    <td class="p-2">{{ $item->name }}</td>
                    <td class="p-2">
                        {{ $item->quantity }} {{ $item->unit }}
                        @if($item->isLow())<span class="ml-2 rounded bg-red-100 px-2 py-0.5 text-xs text-red-700">Low</span>@endif
                    </td>
                    <td class="p-2">{{ $item->reorder_level }}</td>
                    <td class="p-2"><a href="{{ route('inventory-items.show', $item) }}" class="bg-blue-600 text-white px-3 py-1 rounded">Open</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $items->links() }}</div>
        @else
            <p class="text-gray-500">No items yet. Add stationery, cables or anything you stock.</p>
        @endif
    </div>
</div>
@endsection
