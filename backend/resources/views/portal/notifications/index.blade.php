@extends('layouts.portal')

@section('title', __('Notifications'))
@section('page_title', __('Notifications'))

@section('content')

<div class="space-y-4">

    <div class="flex items-center justify-between">
        <p class="text-slate-500">{{ __('Salary and other updates for you') }}</p>

        @if($notifications->contains(fn ($n) => $n->read_at === null))
            <form method="POST" action="{{ route('portal.notifications.readall') }}">
                @csrf
                <button class="eco-logout">{{ __('Mark all as read') }}</button>
            </form>
        @endif
    </div>

    @forelse($notifications as $n)
        <a href="{{ route('portal.notifications.open', $n) }}"
           class="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:shadow-lg {{ $n->read_at ? '' : 'border-l-4 border-l-emerald-500' }}">
            <p class="font-semibold text-slate-900">{{ __($n->title) }}</p>
            @if($n->body)
                <p class="mt-1 text-sm text-slate-600">{{ $n->body }}</p>
            @endif
            <p class="mt-2 text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</p>
        </a>
    @empty
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">
            {{ __('No notifications yet.') }}
        </div>
    @endforelse

    {{ $notifications->links() }}

</div>

@endsection
