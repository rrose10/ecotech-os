@extends('layouts.app')
@section('title', $reminder->exists ? 'Edit reminder' : 'Add reminder')
@section('content')
<div class="max-w-3xl mx-auto p-4 sm:p-8">
    <h1 class="text-3xl font-bold mb-6">{{ $reminder->exists ? 'Edit reminder' : 'Add reminder' }}</h1>

    <form action="{{ $reminder->exists ? route('payment-reminders.update', $reminder) : route('payment-reminders.store') }}" method="POST">
        @csrf
        @if($reminder->exists) @method('PUT') @endif

        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block mb-2 font-semibold">What to pay</label>
                <input type="text" name="title" value="{{ old('title', $reminder->title) }}" class="w-full border rounded p-3" placeholder="Internet bill, Office rent, Canva subscription…" required>
                @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block mb-2 font-semibold">Category</label>
                <input type="text" name="category" value="{{ old('category', $reminder->category) }}" class="w-full border rounded p-3" placeholder="Utility, Rent, Software…">
            </div>
            <div>
                <label class="block mb-2 font-semibold">Amount (BDT) <span class="text-sm font-normal text-gray-500">optional</span></label>
                <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $reminder->amount) }}" class="w-full border rounded p-3">
                @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block mb-2 font-semibold">Next due date</label>
                <input type="date" name="due_date" value="{{ old('due_date', optional($reminder->due_date)->format('Y-m-d')) }}" class="w-full border rounded p-3" required>
                @error('due_date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block mb-2 font-semibold">Repeats</label>
                <select name="repeat" class="w-full border rounded p-3">
                    @foreach(\App\Models\PaymentReminder::REPEATS as $value => $label)
                        <option value="{{ $value }}" @selected(old('repeat', $reminder->repeat) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Remind me before (days)</label>
                <input type="number" min="0" max="60" name="remind_days" value="{{ old('remind_days', $reminder->remind_days) }}" class="w-full border rounded p-3" required>
                @error('remind_days')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 font-semibold">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $reminder->is_active)) class="h-5 w-5">
                    Active
                </label>
            </div>
            <div class="md:col-span-2">
                <label class="block mb-2 font-semibold">Notes</label>
                <textarea name="notes" rows="3" class="w-full border rounded p-3">{{ old('notes', $reminder->notes) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded">Save reminder</button>
            <a href="{{ route('payment-reminders.index') }}" class="px-6 py-3 rounded border">Cancel</a>
        </div>
    </form>
</div>
@endsection
