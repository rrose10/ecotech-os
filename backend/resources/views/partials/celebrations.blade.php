@php
    $celebrations = \App\Support\Celebrations::today();
@endphp

@if($celebrations['birthdays']->isNotEmpty() || $celebrations['anniversaries']->isNotEmpty())
    <div class="rounded-2xl border border-pink-200 bg-gradient-to-r from-pink-50 to-amber-50 p-5 text-slate-800">
        <div class="space-y-3">
            @foreach($celebrations['birthdays'] as $person)
                <div class="flex items-center gap-3">
                    <x-avatar :name="$person->name" :photo="$person->photo" size="h-11 w-11" />
                    <p class="font-semibold">🎂 Today is {{ $person->name }}'s birthday. Wish them well!</p>
                </div>
            @endforeach

            @foreach($celebrations['anniversaries'] as $person)
                <div class="flex items-center gap-3">
                    <x-avatar :name="$person->name" :photo="$person->photo" size="h-11 w-11" />
                    <p class="font-semibold">🎉 {{ $person->name }} completes {{ $person->years_with_us }} {{ \Illuminate\Support\Str::plural('year', $person->years_with_us) }} at EcoTech today.</p>
                </div>
            @endforeach
        </div>
    </div>
@endif
