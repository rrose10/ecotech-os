@extends('layouts.app')
@section('title', $asset->exists ? 'Edit Asset' : 'Add Asset')
@section('content')
<div class="max-w-3xl mx-auto p-8">
    <h1 class="text-3xl font-bold mb-6">{{ $asset->exists ? 'Edit Asset' : 'Add Asset' }}</h1>

    <form action="{{ $asset->exists ? route('assets.update', $asset) : route('assets.store') }}" method="POST">
        @csrf
        @if($asset->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block mb-2 font-semibold">Asset code</label>
                <input type="text" name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}" class="w-full border rounded p-3" required>
                @error('asset_code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block mb-2 font-semibold">Name</label>
                <input type="text" name="name" value="{{ old('name', $asset->name) }}" class="w-full border rounded p-3" required>
                @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block mb-2 font-semibold">Category</label>
                <input type="text" name="category" value="{{ old('category', $asset->category) }}" class="w-full border rounded p-3" placeholder="Laptop, Monitor, Furniture…">
            </div>
            <div>
                <label class="block mb-2 font-semibold">Purchase date</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}" class="w-full border rounded p-3">
            </div>
            <div>
                <label class="block mb-2 font-semibold">Purchase price (BDT)</label>
                <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $asset->purchase_price) }}" class="w-full border rounded p-3">
            </div>
            <div>
                <label class="block mb-2 font-semibold">Status</label>
                <select name="status" class="w-full border rounded p-3">
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected(old('status', $asset->status) === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block mb-2 font-semibold">Assigned to <span class="text-sm font-normal text-gray-500">(only when status is Assigned)</span></label>
                <select name="employee_id" class="w-full border rounded p-3">
                    <option value="">— Nobody —</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}" @selected((string) old('employee_id', $asset->employee_id) === (string) $e->id)>{{ $e->name }} ({{ $e->employee_id }})</option>
                    @endforeach
                </select>
                @error('employee_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block mb-2 font-semibold">Notes</label>
                <textarea name="notes" rows="3" class="w-full border rounded p-3">{{ old('notes', $asset->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded">Save Asset</button>
            <a href="{{ route('assets.index') }}" class="px-6 py-3 rounded border">Cancel</a>
        </div>
    </form>
</div>
@endsection
