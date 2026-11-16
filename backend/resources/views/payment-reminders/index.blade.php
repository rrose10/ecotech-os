@extends('layouts.app')
@section('title', 'Payment reminders')
@section('content')
<div class="p-4 sm:p-8">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold">Payment reminders</h1>
            <p class="text-sm text-gray-500">Internet, rent, software and other bills that must be paid on time.</p>
        </div>
        <a href="{{ route('payment-reminders.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Add reminder</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->has('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ $errors->first('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
        @if($reminders->count())
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Payment</th>
                    <th class="text-left p-2">Amount</th>
                    <th class="text-left p-2">Due date</th>
                    <th class="text-left p-2">Repeats</th>
                    <th class="text-left p-2">Status</th>
                    <th class="text-left p-2">Action</th>
                </tr>
            </thead>
            <tbody>
            @foreach($reminders as $r)
                @php
                    $state = $r->state();
                    $color = ['Overdue' => 'bg-red-100 text-red-700', 'Due soon' => 'bg-amber-100 text-amber-700', 'Upcoming' => 'bg-blue-100 text-blue-700', 'Done' => 'bg-gray-100 text-gray-600'][$state];
                    $left = $r->daysLeft();
                @endphp
                <tr class="border-b">
                    <td class="p-2">
                        <p class="font-semibold">{{ $r->title }}</p>
                        @if($r->category)<p class="text-xs text-gray-500">{{ $r->category }}</p>@endif
                    </td>
                    <td class="p-2">{{ $r->amount !== null ? '৳'.number_format($r->amount, 2) : '—' }}</td>
                    <td class="p-2">
                        {{ $r->due_date->format('d M Y') }}
                        @if($r->is_active)
                            <p class="text-xs text-gray-500">{{ $left < 0 ? abs($left).' days late' : ($left === 0 ? 'today' : 'in '.$left.' days') }}</p>
                        @endif
                    </td>
                    <td class="p-2">{{ \App\Models\PaymentReminder::REPEATS[$r->repeat] ?? $r->repeat }}</td>
                    <td class="p-2"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $color }}">{{ $state }}</span></td>
                    <td class="p-2">
                        <div class="flex flex-wrap gap-2">
                            @if($r->is_active)
                                <form action="{{ route('payment-reminders.paid', $r) }}" method="POST">
                                    @csrf
                                    <button class="bg-green-600 text-white px-3 py-1 rounded">Mark paid</button>
                                </form>
                            @endif
                            <a href="{{ route('payment-reminders.edit', $r) }}" class="bg-blue-600 text-white px-3 py-1 rounded">Edit</a>
                            <form action="{{ route('payment-reminders.destroy', $r) }}" method="POST" onsubmit="return confirm('Delete this reminder?')">
                                @csrf @method('DELETE')
                                <button class="bg-red-600 text-white px-3 py-1 rounded">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $reminders->links() }}</div>
        @else
            <p class="text-gray-500">No reminders yet. Add your internet bill, office rent or software subscriptions.</p>
        @endif
    </div>
</div>
@endsection
