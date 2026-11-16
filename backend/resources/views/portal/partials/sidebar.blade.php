@php
    $items = [
        ['portal.dashboard',  'portal.dashboard',  'Dashboard',   'home'],
        ['portal.profile',    'portal.profile',    'My profile',  'profile'],
        ['portal.attendance', 'portal.attendance', 'My attendance','attendance'],
        ['portal.leaves',     'portal.leaves*',    'My leaves',   'leaves'],
        ['portal.salary',     'portal.salary',     'My payroll',  'payroll'],
        ['portal.notices',    'portal.notices*',   'Team notices','notice'],
    ];
@endphp

<aside id="eco-sidebar"
       class="eco-sidebar eco-drawer flex w-64 shrink-0 flex-col lg:sticky lg:top-4 lg:h-[calc(100vh-2rem)]">

    <div class="flex items-center gap-3 px-5 pb-4 pt-6">
        <span class="eco-logo-tile flex h-11 w-11 items-center justify-center rounded-2xl"><img src="{{ asset('images/ecotech-mark.png') }}" alt="EcoTech" class="h-full w-full object-contain"></span>
        <div>
            <p class="text-lg font-extrabold leading-tight tracking-tight">EcoTech</p>
            <p class="text-xs text-white/50">Team portal</p>
        </div>
    </div>

    <nav class="eco-scroll flex-1 overflow-y-auto px-3 pt-2">
        @foreach($items as [$route, $pattern, $text, $icon])
            <a href="{{ route($route) }}"
               class="eco-nav-link"
               @if(request()->routeIs($pattern)) aria-current="page" @endif>
                @include('partials.nav-icon', ['name' => $icon])
                {{ $text }}
            </a>
        @endforeach
    </nav>

    <div class="p-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full rounded-xl border border-white/15 px-4 py-2.5 text-sm font-semibold text-white/80 transition hover:bg-white/10">
                Log out
            </button>
        </form>
    </div>
</aside>
