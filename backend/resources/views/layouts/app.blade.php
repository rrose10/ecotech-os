@php
    $groups = [
        'Main' => [
            ['dashboard',        'dashboard',     'Dashboard',        'dashboard'],
            ['employees.index',  'employees.*',   'Team',        'employees'],
            ['departments.index','departments.*', 'Departments',      'departments'],
            ['attendances.index','attendances.*', 'Attendance',       'attendance'],
            ['leave-types.index','leave-types.*', 'Leave types',      'calendar'],
            ['leaves.index',     'leaves.*',      'Leaves',           'leaves'],
            ['leave-balances.index','leave-balances.*','Leave balance','balance'],
        ],
        'Finance' => [
            ['expense-categories.index','expense-categories.*','Expense categories','tag'],
            ['expenses.index',   'expenses.*',    'Expenses',         'expense'],
            ['income-categories.index','income-categories.*','Income categories','tag'],
            ['incomes.index',    'incomes.*',     'Incomes',          'income'],
            ['advance-salaries.index','advance-salaries.*','Advance salary','advance'],
            ['employee-loans.index','employee-loans.*','Team loans','loan'],
            ['payrolls.index',   'payrolls.*',    'Payroll',          'payroll'],
            ['bank-accounts.index','bank-accounts.*','Cash & bank',   'bank'],
        ],
        'Office' => [
            ['notices.index',    'notices.*',     'Notices',          'notice'],
            ['activity-logs.index','activity-logs.*','Activity logs', 'logs'],
            ['assets.index', 'assets.*', 'Assets', 'assets'],
            ['inventory-items.index', 'inventory-items.*', 'Inventory', 'inventory'],
        ],
        'Reports' => [
            ['reports.index', 'reports.*', 'Reports', 'reports'],
            ['settings.edit', 'settings.*', 'Settings', 'settings'],
        ],
    ];
    // Settings is for super admins only
    if (auth()->user()?->role !== 'super_admin') {
        $groups['Reports'] = array_values(array_filter($groups['Reports'], fn ($i) => $i[0] !== 'settings.edit'));
        $groups['Office'] = array_values(array_filter($groups['Office'], fn ($i) => $i[0] !== 'activity-logs.index'));
    }
    $brand = \App\Models\Setting::get('company_name', 'EcoTech');

    if (auth()->user()?->role === 'hr') {
        $hrAllowed = ['employees.index','departments.index','attendances.index','leave-types.index','leaves.index','leave-balances.index','notices.index'];
        $groups = collect($groups)
            ->map(fn ($items) => array_values(array_filter($items, fn ($i) => in_array($i[0], $hrAllowed, true))))
            ->filter()->all();
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'EcoTech OS')</title>

    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="eco min-h-screen antialiased">

<div class="flex min-h-screen gap-4 p-3 lg:p-4">

    <div id="eco-scrim" class="eco-scrim hidden lg:hidden" data-eco-close></div>

    <!-- Sidebar -->
    <aside id="eco-sidebar"
           class="eco-sidebar eco-drawer flex w-64 shrink-0 flex-col lg:sticky lg:top-4 lg:h-[calc(100vh-2rem)]">

        <div class="flex items-center gap-3 px-5 pb-4 pt-6">
            <span class="eco-brand-mark flex h-11 w-11 items-center justify-center rounded-2xl text-lg font-extrabold">{{ strtoupper(mb_substr($brand, 0, 1)) }}</span>
            <div>
                <p class="text-lg font-extrabold leading-tight tracking-tight">{{ $brand }}</p>
                <p class="text-xs text-white/50">Office management</p>
            </div>
        </div>

        <nav class="eco-scroll flex-1 overflow-y-auto px-3 pb-6">
            @foreach($groups as $label => $items)
                @if($label !== 'Main')
                    <p class="eco-nav-label">{{ $label }}</p>
                @endif

                @foreach($items as [$route, $pattern, $text, $icon])
                    @if($route)
                        <a href="{{ route($route) }}"
                           class="eco-nav-link"
                           @if(request()->routeIs($pattern)) aria-current="page" @endif>
                            @include('partials.nav-icon', ['name' => $icon])
                            {{ $text }}
                        </a>
                    @else
                        <div class="eco-nav-link is-soon" title="Coming soon">
                            @include('partials.nav-icon', ['name' => $icon])
                            {{ $text }}
                            <span class="eco-soon">Soon</span>
                        </div>
                    @endif
                @endforeach
            @endforeach
        </nav>
    </aside>

    <!-- Main -->
    <div class="flex min-w-0 flex-1 flex-col gap-4">

        <header class="eco-topbar sticky top-3 z-30 flex items-center justify-between gap-4 px-4 py-3 lg:top-4 lg:px-6 lg:py-4">
            <div class="flex items-center gap-3">
                <button type="button" id="eco-menu" class="eco-logout !px-3 lg:hidden" aria-label="Open menu">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight lg:text-2xl">
                        @hasSection('page_title')@yield('page_title')@elseif(View::hasSection('title'))@yield('title')@else Dashboard @endif
                    </h2>
                    <p class="hidden text-sm text-gray-500 sm:block">
                        {{ now()->format('l, d F Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-bold leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ Auth::user()->role === 'employee' ? 'Team member' : ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</p>
                </div>
                @include('partials.theme-toggle')
                <x-avatar :name="Auth::user()->name" :photo="optional(Auth::user()->employee)->photo" />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="eco-logout">Log out</button>
                </form>
            </div>
        </header>

        <main class="min-w-0 flex-1 px-1 pb-6 lg:px-2">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
