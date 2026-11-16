<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
        <title>{{ config('app.name', 'EcoTech OS') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="eco min-h-screen antialiased">
@include('partials.bg-tech')
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">

            <div class="mb-8 flex flex-col items-center gap-3 text-center">
                <span class="eco-logo-tile eco-logo-tile-lg flex items-center justify-center rounded-3xl">
                    <img src="{{ asset('images/ecotech-logo.png') }}" alt="EcoTech" class="h-full w-full object-contain">
                </span>
                <div>
                    <p class="text-2xl font-extrabold leading-tight tracking-tight">EcoTech OS</p>
                    <p class="text-sm text-gray-500">Sign in to your workspace</p>
                </div>
            </div>

            <div class="eco-auth-card w-full max-w-md px-8 py-8">
                {{ $slot }}
            </div>

        </div>
    </body>
</html>
