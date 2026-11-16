@props(['name' => '', 'photo' => null, 'size' => 'h-10 w-10', 'text' => 'text-sm'])

@if($photo)
    <img src="{{ asset('storage/' . $photo) }}" alt="{{ $name }}" loading="lazy"
         {{ $attributes->merge(['class' => "$size shrink-0 rounded-full object-cover ring-2 ring-white/60"]) }}>
@else
    <span {{ $attributes->merge(['class' => "eco-avatar $size $text flex shrink-0 items-center justify-center rounded-full font-bold"]) }}>
        {{ strtoupper(mb_substr(trim($name) ?: '?', 0, 1)) }}
    </span>
@endif
