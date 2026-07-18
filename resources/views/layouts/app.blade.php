<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'EcoTech OS')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans antialiased">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white shadow-xl">

        <div class="p-6 border-b border-slate-700">
            <h1 class="text-3xl font-bold tracking-wide">
                EcoTech
            </h1>

            <p class="text-slate-400 text-sm mt-1">
                Office Management System
            </p>
        </div>

        <nav class="mt-6">

            <a href="{{ route('dashboard') }}"
               class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
               {{ request()->routeIs('dashboard')
                    ? 'bg-blue-600 text-white shadow'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                📊 Dashboard
            </a>

            <a href="{{ route('employees.index') }}"
               class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
               {{ request()->routeIs('employees.*')
                    ? 'bg-blue-600 text-white shadow'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                👥 Employees
            </a>

            <a href="{{ route('departments.index') }}"
               class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
               {{ request()->routeIs('departments.*')
                    ? 'bg-blue-600 text-white shadow'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                🏢 Departments
            </a>

            <a href="{{ route('attendances.index') }}"
               class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
               {{ request()->routeIs('attendances.*')
                    ? 'bg-blue-600 text-white shadow'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                🕒 Attendance
            </a>

            <a href="{{ route('leave-types.index') }}"
               class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
               {{ request()->routeIs('leave-types.*')
                    ? 'bg-blue-600 text-white shadow'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                📅 Leave Types
            </a>

            <a href="{{ route('leave-requests.index') }}"
               class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
               {{ request()->routeIs('leave-requests.*')
                    ? 'bg-blue-600 text-white shadow'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                📝 Leave Requests
            </a>

            <hr class="my-5 border-slate-700">

<p class="px-6 mb-3 text-xs uppercase tracking-widest text-slate-500">
    Finance
</p>

<a href="{{ route('expense-categories.index') }}"
   class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
   {{ request()->routeIs('expense-categories.*')
        ? 'bg-blue-600 text-white shadow'
        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    💰 Expense Categories
</a>

<a href="{{ route('expenses.index') }}"
   class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
   {{ request()->routeIs('expenses.*')
        ? 'bg-blue-600 text-white shadow'
        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    💸 Expenses
</a>

<a href="{{ route('income-categories.index') }}"
   class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
   {{ request()->routeIs('income-categories.*')
        ? 'bg-blue-600 text-white shadow'
        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    💵 Income Categories
</a>

<a href="{{ route('incomes.index') }}"
   class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
   {{ request()->routeIs('incomes.*')
        ? 'bg-blue-600 text-white shadow'
        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    💰 Incomes
</a>

<a href="{{ route('payrolls.index') }}"
   class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
   {{ request()->routeIs('payrolls.*')
        ? 'bg-blue-600 text-white shadow'
        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    💼 Payroll
</a>

<a href="{{ route('bank-accounts.index') }}"
   class="block mx-3 mb-2 rounded-lg px-5 py-3 transition
   {{ request()->routeIs('bank-accounts.*')
        ? 'bg-blue-600 text-white shadow'
        : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
    🏦 Cash & Bank
</a>

<hr class="my-5 border-slate-700">

<p class="px-6 mb-3 text-xs uppercase tracking-widest text-slate-500">
    Office
</p>

<div class="px-6 py-2 text-slate-500">
    💻 Assets
</div>

<div class="px-6 py-2 text-slate-500">
    📦 Inventory
</div>

<hr class="my-5 border-slate-700">

<p class="px-6 mb-3 text-xs uppercase tracking-widest text-slate-500">
    Reports
</p>

<div class="px-6 py-2 text-slate-500">
    📊 Reports
</div>

<div class="px-6 py-2 text-slate-500">
    ⚙️ Settings
</div>
        </nav>

    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col">

        <!-- Topbar -->
        <header class="bg-white shadow-sm border-b">

            <div class="flex justify-between items-center px-8 py-5">

                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        @yield('page_title', 'Dashboard')
                    </h2>

                    <p class="text-sm text-gray-500">
                        Welcome back, {{ Auth::user()->name }}
                    </p>
                </div>

                <div class="flex items-center gap-4">

                    <div class="text-right">
                        <p class="font-semibold text-gray-700">
                            {{ Auth::user()->name }}
                        </p>

                        <p class="text-sm text-gray-500">
                            {{ ucfirst(Auth::user()->role) }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            class="rounded-lg bg-red-600 px-5 py-2 text-white hover:bg-red-700 transition">
                            Logout
                        </button>

                    </form>

                </div>

            </div>

        </header>

        <!-- Page Content -->
        <main class="flex-1 p-8">
            @yield('content')
        </main>

    </div>

</div>

</body>
</html>