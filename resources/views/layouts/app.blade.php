<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoTech OS</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white">

        <div class="p-6 text-2xl font-bold border-b border-slate-700">
            EcoTech OS
        </div>

        <nav class="p-4 space-y-2">

            <a href="/"
               class="block px-4 py-2 rounded hover:bg-slate-800">
                Dashboard
            </a>

            <a href="{{ route('departments.index') }}"
               class="block px-4 py-2 rounded hover:bg-slate-800">
                Departments
            </a>

            <a href="{{ route('employees.index') }}"
               class="block px-4 py-2 rounded hover:bg-slate-800">
                Employees
            </a>

            <a href="#"
               class="block px-4 py-2 rounded hover:bg-slate-800">
                Attendance
            </a>

            <a href="#"
               class="block px-4 py-2 rounded hover:bg-slate-800">
                Finance
            </a>

            <a href="#"
               class="block px-4 py-2 rounded hover:bg-slate-800">
                Inventory
            </a>

            <a href="#"
               class="block px-4 py-2 rounded hover:bg-slate-800">
                Assets
            </a>

            <a href="#"
               class="block px-4 py-2 rounded hover:bg-slate-800">
                Settings
            </a>

        </nav>

    </aside>

    <!-- Main Content -->
    <main class="flex-1">

        <header class="bg-white shadow p-6 flex justify-between items-center">

            <h1 class="text-2xl font-bold">
                EcoTech Office Management System
            </h1>

            <div class="font-semibold">
                Super Admin
            </div>

        </header>

        <div class="p-8">
            @yield('content')
        </div>

    </main>

</div>

</body>
</html>