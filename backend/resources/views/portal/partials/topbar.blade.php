<header class="flex items-center justify-between border-b border-slate-200 bg-white px-8 py-5">

    <div>

        <h1 class="text-2xl font-semibold text-slate-900">
            @yield('page_title', 'Dashboard')
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            {{ now()->format('l, d F Y') }}
        </p>

    </div>

    <div class="flex items-center gap-4">

        <div class="text-right">

            <p class="font-semibold text-slate-900">
                {{ auth()->user()->name }}
            </p>

            <p class="text-sm text-slate-500">
                {{ ucfirst(auth()->user()->role) }}
            </p>

        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                class="rounded-xl border border-red-200 px-4 py-2 text-red-600 transition hover:bg-red-50">
                Logout
            </button>

        </form>

    </div>

</header>