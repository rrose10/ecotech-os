<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Employee Portal')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100">

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    @include('portal.partials.sidebar')

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col">

        {{-- Topbar --}}
        @include('portal.partials.topbar')

        {{-- Page Content --}}
        <main class="flex-1 p-8">
            @yield('content')
        </main>

    </div>

</div>

</body>
</html>