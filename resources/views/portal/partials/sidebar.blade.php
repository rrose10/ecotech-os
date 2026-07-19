<aside class="flex h-screen w-64 flex-col bg-slate-900 text-white">

    <!-- Logo -->
    <div class="border-b border-slate-800 px-6 py-6">
        <h1 class="text-2xl font-bold tracking-wide">
            EcoTech OS
        </h1>

        <p class="mt-1 text-sm text-slate-400">
            Employee Portal
        </p>
    </div>

    <!-- Menu -->
    <nav class="flex-1 space-y-2 px-4 py-6">

        <!-- Dashboard -->
        <a href="{{ route('portal.dashboard') }}"
           class="flex items-center rounded-xl px-4 py-3 transition
           {{ request()->routeIs('portal.dashboard')
                ? 'bg-blue-600 text-white'
                : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">

            <span class="mr-3 text-lg">🏠</span>

            Dashboard

        </a>

        <!-- My Profile -->
        <a href="{{ route('portal.profile') }}"
           class="flex items-center rounded-xl px-4 py-3 transition
           {{ request()->routeIs('portal.profile')
                ? 'bg-blue-600 text-white'
                : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">

            <span class="mr-3 text-lg">👤</span>

            My Profile

        </a>

        <!-- Attendance -->
        <a href="{{ route('portal.attendance') }}"
           class="flex items-center rounded-xl px-4 py-3 transition
           {{ request()->routeIs('portal.attendance')
                ? 'bg-blue-600 text-white'
                : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">

            <span class="mr-3 text-lg">🕒</span>

            Attendance

        </a>

        <!-- Salary -->
        <a href="{{ route('portal.salary') }}"
           class="flex items-center rounded-xl px-4 py-3 transition
           {{ request()->routeIs('portal.salary')
                ? 'bg-blue-600 text-white'
                : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">

            <span class="mr-3 text-lg">💰</span>

            Salary

        </a>

        <!-- Notices -->
        <a href="#"
           class="flex items-center rounded-xl px-4 py-3 transition text-slate-200 hover:bg-slate-800 hover:text-white">

            <span class="mr-3 text-lg">📢</span>

            Notices

        </a>

        <!-- Settings -->
        <a href="#"
           class="flex items-center rounded-xl px-4 py-3 transition text-slate-200 hover:bg-slate-800 hover:text-white">

            <span class="mr-3 text-lg">⚙️</span>

            Settings

        </a>

    </nav>

</aside>