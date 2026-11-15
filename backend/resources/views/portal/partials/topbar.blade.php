<header class="eco-topbar sticky top-3 z-30 flex items-center justify-between gap-4 px-4 py-3 lg:top-4 lg:px-6 lg:py-4">

    <div class="flex items-center gap-3">
        <button type="button" id="eco-menu" class="eco-logout !px-3 lg:hidden" aria-label="Open menu">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>

        <div>
            <h1 class="text-xl font-extrabold tracking-tight lg:text-2xl">
                @yield('page_title', 'Dashboard')
            </h1>
            <p class="hidden text-sm text-slate-500 sm:block">
                {{ now()->format('l, d F Y') }}
            </p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <div class="hidden text-right sm:block">
            <p class="text-sm font-bold leading-tight">{{ auth()->user()->name }}</p>
            <p class="text-xs text-slate-500">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
        </div>

        <span class="eco-avatar flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold">
            {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
        </span>
    </div>

</header>
