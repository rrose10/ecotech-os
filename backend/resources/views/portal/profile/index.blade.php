@extends('layouts.portal')

@section('title', __('My Profile'))

@section('page_title', __('My Profile'))

@section('content')

@if(!$employee)
<div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-600">
    {{ __("No employee profile is linked to your account yet. Please contact the administrator.") }}
</div>
@else

<div class="space-y-6">

    <!-- Profile Header -->
    <div class="bg-white rounded-2xl border border-slate-200 p-8">

        <div class="flex flex-col items-center text-center">

            @if($employee->photo)
                <img src="{{ asset('storage/' . $employee->photo) }}"
                     alt="{{ $employee->name }}"
                     class="h-32 w-32 rounded-full object-cover border-4 border-slate-200 shadow">
            @else
                <div class="flex h-32 w-32 items-center justify-center rounded-full bg-slate-100 border-4 border-slate-200 text-5xl font-bold text-slate-500">
                    {{ strtoupper(substr($employee->name,0,1)) }}
                </div>
            @endif

            <h2 class="mt-5 text-3xl font-bold text-slate-900">
                {{ $employee->name }}
            </h2>

            <p class="mt-1 text-slate-600">
                {{ $employee->designation ?? '--' }}
            </p>

            <span class="mt-3 inline-flex rounded-full bg-blue-100 px-4 py-1 text-sm font-medium text-blue-700">
                {{ $employee->department?->name ?? 'No Department' }}
            </span>

        </div>

    </div>

    <!-- Telegram reminders -->
    @php($tgUser = auth()->user())
    @if(\App\Support\Telegram::configured() && \App\Support\Telegram::username())
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-900">{{ __('Telegram reminders') }}</h3>

        @if($tgUser->telegram_chat_id)
            <p class="mt-2 text-sm text-emerald-700">✅ {{ __('Connected. Check-out reminders come to your Telegram.') }}</p>
            <form method="POST" action="{{ route('portal.telegram.unlink') }}" class="mt-3">
                @csrf @method('DELETE')
                <button class="eco-logout">{{ __('Disconnect') }}</button>
            </form>
        @elseif($tgUser->telegram_link_code)
            <p class="mt-2 text-sm text-slate-600">{{ __('Open this link on your phone and press Start:') }}</p>
            <a class="mt-2 inline-block rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white"
               href="https://t.me/{{ \App\Support\Telegram::username() }}?start={{ $tgUser->telegram_link_code }}">
                {{ __('Open Telegram') }}
            </a>
            <p class="mt-2 text-xs text-slate-500">{{ __('After pressing Start, reload this page in a minute.') }}</p>
        @else
            <p class="mt-2 text-sm text-slate-600">{{ __('Get a Telegram message 5 minutes before your shift ends, so you never forget to check out.') }}</p>
            <form method="POST" action="{{ route('portal.telegram.link') }}" class="mt-3">
                @csrf
                <button class="eco-logout">{{ __('Connect Telegram') }}</button>
            </form>
        @endif
    </div>
    @endif

    <!-- Personal Information -->
    <div class="bg-white rounded-2xl border border-slate-200">

        <div class="border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-900">
                {{ __("Personal Information") }}
            </h3>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">

            <div>
                <p class="text-sm text-slate-500">{{ __("Member ID") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->employee_id }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">{{ __("Email") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->email }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">{{ __("Phone") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->phone ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">{{ __("Gender") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->gender ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">{{ __("Blood Group") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->blood_group ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">{{ __("Date of Birth") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->date_of_birth?->format('d M Y') ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">{{ __("Designation") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->designation ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">{{ __("Department") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->department?->name ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">{{ __("Joining Date") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->joining_date?->format('d M Y') ?? '--' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-slate-500">{{ __("Emergency Contact") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->emergency_contact ?? '--' }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-sm text-slate-500">{{ __("Address") }}</p>
                <p class="mt-1 font-semibold text-slate-900">
                    {{ $employee->address ?? '--' }}
                </p>
            </div>

        </div>

    </div>

</div>

@endif

@endsection