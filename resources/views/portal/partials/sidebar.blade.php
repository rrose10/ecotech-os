<aside class="flex h-screen w-64 flex-col bg-slate-900 text-white">

    {{-- Logo --}}
    <div class="border-b border-slate-800 px-6 py-6">

        <h1 class="text-2xl font-bold tracking-wide">
            EcoTech OS
        </h1>

        <p class="mt-1 text-sm text-slate-400">
            Team Portal
        </p>

    </div>

    {{-- Menu --}}
    <nav class="flex-1 space-y-2 px-4 py-6">

        {{-- Dashboard --}}
        <a href="{{ route('portal.dashboard') }}"
           class="flex items-center rounded-xl px-4 py-3 transition
           {{ request()->routeIs('portal.dashboard')
                ? 'bg-blue-600 text-white shadow-lg'
                : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">

            <span class="mr-3 text-lg">🏠</span>

            Dashboard

        </a>

        {{-- My Profile --}}
        <a href="{{ route('portal.profile') }}"
           class="flex items-center rounded-xl px-4 py-3 transition
           {{ request()->routeIs('portal.profile')
                ? 'bg-blue-600 text-white shadow-lg'
                : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">

            <span class="mr-3 text-lg">👤</span>

            My Profile

        </a>

        {{-- My Attendance --}}
        <a href="{{ route('portal.attendance') }}"
           class="flex items-center rounded-xl px-4 py-3 transition
           {{ request()->routeIs('portal.attendance')
                ? 'bg-blue-600 text-white shadow-lg'
                : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">

            <span class="mr-3 text-lg">🕒</span>

            My Attendance

        </a>

        {{-- My Payroll --}}
        <a href="{{ route('portal.salary') }}"
           class="flex items-center rounded-xl px-4 py-3 transition
           {{ request()->routeIs('portal.salary')
                ? 'bg-blue-600 text-white shadow-lg'
                : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">

            <span class="mr-3 text-lg">💰</span>

            My Payroll

        </a>

        {{-- Team Notices --}}
        <a href="{{ route('portal.notices') }}"
           class="flex items-center rounded-xl px-4 py-3 transition
           {{ request()->routeIs('portal.notices*')
                ? 'bg-blue-600 text-white shadow-lg'
                : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">

            <span class="mr-3 text-lg">📢</span>

            Team Notices

        </a>

    </nav>

    {{-- Footer --}}
    <div class="border-t border-slate-800 p-4">

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="flex w-full items-center justify-center rounded-xl bg-red-600 px-4 py-3 font-medium transition hover:bg-red-700">

                🚪 Logout

            </button>

        </form>

    </div>

</aside>