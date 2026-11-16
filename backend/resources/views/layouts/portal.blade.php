<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Team Portal')</title>

    @include('partials.theme-head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="eco min-h-screen antialiased">

<div class="flex min-h-screen gap-4 p-3 lg:p-4">

    <div id="eco-scrim" class="eco-scrim hidden lg:hidden" data-eco-close></div>

    @include('portal.partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col gap-4">

        @include('portal.partials.topbar')

        <main class="min-w-0 flex-1 px-1 pb-6 lg:px-2">
            @include('partials.alerts')
            @yield('content')
        </main>

    </div>

</div>

@stack('scripts')
</body>
</html>
