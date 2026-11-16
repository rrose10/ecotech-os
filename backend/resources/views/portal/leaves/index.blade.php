@extends('layouts.portal')

@section('title', 'My leaves')
@section('page_title', 'My leaves')

@section('content')

<div class="space-y-6">

    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Balances --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($leaveTypes as $type)
            @php
                $b = $balances->get($type->id);
                $left = $b ? $b->remaining_days : $type->days_per_year;
                $used = $b ? $b->used_days : 0;
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">{{ $type->name }}</p>
                <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $left }}
                    <span class="text-base font-medium text-slate-500">days left</span></p>
                <p class="mt-1 text-xs text-slate-500">{{ $used }} used of {{ $type->days_per_year }} this year</p>
            </div>
        @endforeach
    </div>

    {{-- Request form --}}
    <div class="rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-xl font-bold text-slate-900">Request leave</h2>
        </div>

        <form method="POST" action="{{ route('portal.leaves.store') }}" class="grid gap-5 p-6 md:grid-cols-2">
            @csrf

            <div>
                <label for="leave_type_id" class="mb-1 block text-sm font-semibold text-slate-700">Leave type</label>
                <select id="leave_type_id" name="leave_type_id" required
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5">
                    <option value="">Choose a type</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('leave_type_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="hidden md:block"></div>

            <div>
                <label for="from_date" class="mb-1 block text-sm font-semibold text-slate-700">From</label>
                <input id="from_date" type="date" name="from_date" value="{{ old('from_date') }}" min="{{ today()->toDateString() }}" required
                       class="w-full rounded-xl border border-slate-300 px-3 py-2.5">
                @error('from_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="to_date" class="mb-1 block text-sm font-semibold text-slate-700">To</label>
                <input id="to_date" type="date" name="to_date" value="{{ old('to_date') }}" min="{{ today()->toDateString() }}" required
                       class="w-full rounded-xl border border-slate-300 px-3 py-2.5">
                @error('to_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="reason" class="mb-1 block text-sm font-semibold text-slate-700">Reason (optional)</label>
                <textarea id="reason" name="reason" rows="3" maxlength="1000"
                          class="w-full rounded-xl border border-slate-300 px-3 py-2.5">{{ old('reason') }}</textarea>
                @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700">
                    Send request
                </button>
            </div>
        </form>
    </div>

    {{-- History --}}
    <div class="rounded-2xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-xl font-bold text-slate-900">My requests</h2>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($leaves as $leave)
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $leave->leaveType->name ?? 'Leave' }}
                            <span class="font-normal text-slate-500">· {{ rtrim(rtrim($leave->total_days, '0'), '.') }} {{ \Illuminate\Support\Str::plural('day', (int) $leave->total_days) }}</span></p>
                        <p class="text-sm text-slate-500">
                            {{ $leave->from_date->format('d M Y') }} to {{ $leave->to_date->format('d M Y') }}
                        </p>
                        @if($leave->status === 'Rejected' && $leave->remarks)
                            <p class="mt-1 text-sm text-red-600">Reason: {{ $leave->remarks }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        @php
                            $badge = match($leave->status) {
                                'Approved' => 'bg-green-100 text-green-700',
                                'Rejected' => 'bg-red-100 text-red-700',
                                default => 'bg-yellow-100 text-yellow-700',
                            };
                        @endphp
                        <span class="rounded-full px-3 py-1 text-sm font-medium {{ $badge }}">{{ $leave->status }}</span>

                        @if($leave->status === 'Pending')
                            <form method="POST" action="{{ route('portal.leaves.cancel', $leave) }}"
                                  onsubmit="return confirm('Cancel this leave request?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm font-semibold text-red-600 hover:text-red-700">Cancel</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-slate-500">You have not requested any leave yet.</div>
            @endforelse
        </div>

        @if($leaves->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">{{ $leaves->links() }}</div>
        @endif
    </div>

</div>

@endsection
