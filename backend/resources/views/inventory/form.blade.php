@extends('layouts.app')
@section('title', $item->exists ? 'Edit Item' : 'Add Item')
@section('content')
<div class="max-w-3xl mx-auto p-8">
    <h1 class="text-3xl font-bold mb-6">{{ $item->exists ? 'Edit Item' : 'Add Item' }}</h1>

    <form action="{{ $item->exists ? route('inventory-items.update', $item) : route('inventory-items.store') }}" method="POST">
        @csrf
        @if($item->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block mb-2 font-semibold">SKU</label>
                <input type="text" name="sku" value="{{ old('sku', $item->sku) }}" class="w-full border rounded p-3" required>
                @error('sku')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block mb-2 font-semibold">Name</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" class="w-full border rounded p-3" required>
                @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block mb-2 font-semibold">Unit</label>
                <input type="text" name="unit" value="{{ old('unit', $item->unit) }}" class="w-full border rounded p-3" required>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Reorder level</label>
                <input type="number" min="0" name="reorder_level" value="{{ old('reorder_level', $item->reorder_level) }}" class="w-full border rounded p-3">
            </div>
            @unless($item->exists)
            <div>
                <label class="block mb-2 font-semibold">Opening stock</label>
                <input type="number" min="0" name="quantity" value="{{ old('quantity', 0) }}" class="w-full border rounded p-3">
            </div>
            @endunless
            <div class="md:col-span-2">
                <label class="block mb-2 font-semibold">Notes</label>
                <textarea name="notes" rows="3" class="w-full border rounded p-3">{{ old('notes', $item->notes) }}</textarea>
            </div>
        </div>
        @if($item->exists)
            <p class="mt-3 text-sm text-gray-500">To change the quantity, use Stock in / Stock out on the item page so every change is recorded.</p>
        @endif

        <div class="mt-6 flex gap-3">
            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded">Save Item</button>
            <a href="{{ route('inventory-items.index') }}" class="px-6 py-3 rounded border">Cancel</a>
        </div>
    </form>
</div>
@endsection
