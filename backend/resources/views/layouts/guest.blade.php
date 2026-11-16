<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EcoTech OS') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="eco min-h-screen antialiased">
@include('partials.bg-tech')
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">

            <div class="mb-8 flex items-center gap-3">
                <span class="eco-brand-mark flex h-12 w-12 items-center justify-center rounded-2xl text-xl font-extrabold">E</span>
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
