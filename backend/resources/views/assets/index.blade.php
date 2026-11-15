@extends('layouts.app')
@section('title', 'Assets')
@section('content')
<div class="p-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold">Assets</h1>
            <p class="text-sm text-gray-500">Active asset value: ৳{{ number_format($totalValue, 2) }}</p>
        </div>
        <a href="{{ route('assets.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Add Asset</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, code, category" class="border rounded p-2 w-64">
        <select name="status" class="border rounded p-2">
            <option value="">All statuses</option>
            @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        <button class="bg-green-600 text-white px-4 py-2 rounded">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
        @if($assets->count())
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Code</th>
                    <th class="text-left p-2">Name</th>
                    <th class="text-left p-2">Category</th>
                    <th class="text-left p-2">Status</th>
                    <th class="text-left p-2">Assigned to</th>
                    <th class="text-left p-2">Price</th>
                    <th class="text-left p-2">Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($assets as $asset)
                <tr class="border-b">
                    <td class="p-2">{{ $asset->asset_code }}</td>
                    <td class="p-2">{{ $asset->name }}</td>
                    <td class="p-2">{{ $asset->category ?? '—' }}</td>
                    <td class="p-2">{{ $asset->status }}</td>
                    <td class="p-2">{{ $asset->employee->name ?? '—' }}</td>
                    <td class="p-2">৳{{ number_format($asset->purchase_price, 2) }}</td>
                    <td class="p-2 flex gap-2">
                        <a href="{{ route('assets.edit', $asset) }}" class="bg-blue-600 text-white px-3 py-1 rounded">Edit</a>
                        <form action="{{ route('assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Delete this asset?')">
                            @csrf @method('DELETE')
                            <button class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $assets->links() }}</div>
        @else
            <p class="text-gray-500">No assets yet. Add laptops, monitors, furniture or anything the office owns.</p>
        @endif
    </div>
</div>
@endsection
