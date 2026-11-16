{{-- Futuristic backdrop: floating tech icons, a data network and glowing orbs behind the glass. --}}
@php
    $svg = [
        'cpu'      => '<rect x="6" y="6" width="12" height="12" rx="2"/><rect x="9.5" y="9.5" width="5" height="5" rx="1"/><path d="M9 2v3M15 2v3M9 19v3M15 19v3M2 9h3M2 15h3M19 9h3M19 15h3"/>',
        'code'     => '<path d="M8 7l-5 5 5 5M16 7l5 5-5 5M14 4l-4 16"/>',
        'cloud'    => '<path d="M7 18a4.5 4.5 0 0 1-.6-8.96A6 6 0 0 1 18 9.5a4 4 0 0 1-.5 8.5z"/>',
        'wifi'     => '<path d="M2.5 9a15 15 0 0 1 19 0M5.5 12.5a10.5 10.5 0 0 1 13 0M8.6 16a6 6 0 0 1 6.8 0"/><circle cx="12" cy="19.5" r="1"/>',
        'atom'     => '<circle cx="12" cy="12" r="1.6"/><ellipse cx="12" cy="12" rx="10" ry="4.2"/><ellipse cx="12" cy="12" rx="10" ry="4.2" transform="rotate(60 12 12)"/><ellipse cx="12" cy="12" rx="10" ry="4.2" transform="rotate(120 12 12)"/>',
        'hex'      => '<path d="M12 2.5l8.2 4.75v9.5L12 21.5l-8.2-4.75v-9.5z"/><path d="M12 8l4 2.3v4.4L12 17l-4-2.3v-4.4z"/>',
        'database' => '<ellipse cx="12" cy="5.5" rx="8" ry="3"/><path d="M4 5.5v13c0 1.7 3.6 3 8 3s8-1.3 8-3v-13M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
        'shield'   => '<path d="M12 2.5l8 3v6c0 5-3.4 8.7-8 10-4.6-1.3-8-5-8-10v-6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
        'bolt'     => '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>',
        'globe'    => '<circle cx="12" cy="12" r="9.5"/><path d="M2.5 12h19M12 2.5c3 3 3 16 0 19M12 2.5c-3 3-3 16 0 19"/>',
        'terminal' => '<rect x="2.5" y="4" width="19" height="16" rx="3"/><path d="M7 9.5l3 2.5-3 2.5M12.5 15h4.5"/>',
        'circuit'  => '<circle cx="5" cy="6" r="2"/><circle cx="19" cy="18" r="2"/><circle cx="19" cy="6" r="2"/><path d="M7 6h10M19 8v8M5 8v6a4 4 0 0 0 4 4h8"/>',
        'lock'     => '<rect x="4.5" y="10.5" width="15" height="10" rx="2.5"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/>',
        'radar'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><path d="M12 12l6-6"/>',
    ];

    // name, left %, top %, size px, colour, float seconds, delay s, blur px, layer, hide on phones
    $icons = [
        ['cpu',      6, 12, 92, 'b', 19, 0, 0, 'near', 0],
        ['code',    28,  7, 64, 'v', 23, 2, 1, 'far',  1],
        ['cloud',   47, 14, 104,'c', 27, 1, 2, 'far',  0],
        ['atom',    71,  9, 82, 'v', 21, 3, 0, 'near', 0],
        ['wifi',    90, 20, 58, 'b', 25, 0, 1, 'far',  1],
        ['hex',     14, 38, 70, 'c', 22, 4, 2, 'far',  1],
        ['database',36, 34, 76, 'b', 26, 2, 0, 'near', 0],
        ['shield',  62, 40, 88, 'v', 24, 1, 1, 'near', 0],
        ['bolt',    84, 46, 62, 'c', 18, 5, 0, 'far',  1],
        ['globe',    5, 62, 112,'v', 30, 0, 3, 'far',  0],
        ['terminal',24, 70, 84, 'b', 20, 3, 0, 'near', 0],
        ['circuit', 46, 66, 98, 'c', 28, 1, 2, 'far',  1],
        ['lock',    68, 74, 60, 'b', 22, 4, 1, 'near', 1],
        ['radar',   86, 78, 96, 'v', 26, 2, 0, 'near', 0],
        ['cpu',     55, 88, 66, 'v', 23, 5, 2, 'far',  1],
        ['code',    10, 90, 58, 'c', 19, 1, 1, 'far',  1],
    ];
@endphp
<div class="eco-bg" aria-hidden="true">
    <span class="eco-orb eco-orb-a"></span>
    <span class="eco-orb eco-orb-b"></span>
    <span class="eco-orb eco-orb-c"></span>

    <svg class="eco-net" viewBox="0 0 1000 640" preserveAspectRatio="xMidYMid slice" fill="none">
        <g class="eco-net-lines" stroke="currentColor" stroke-width="1.2" stroke-linecap="round">
            <path d="M40 120 L210 220 L380 150 L560 260 L760 170 L960 250"/>
            <path d="M120 520 L300 430 L470 500 L640 410 L830 480 L980 400"/>
            <path d="M210 220 L300 430 M560 260 L640 410 M380 150 L470 500 M760 170 L830 480"/>
        </g>
        <g class="eco-net-nodes" fill="currentColor">
            <circle cx="40" cy="120" r="4"/><circle cx="210" cy="220" r="5"/><circle cx="380" cy="150" r="4"/>
            <circle cx="560" cy="260" r="6"/><circle cx="760" cy="170" r="4"/><circle cx="960" cy="250" r="5"/>
            <circle cx="120" cy="520" r="4"/><circle cx="300" cy="430" r="5"/><circle cx="470" cy="500" r="4"/>
            <circle cx="640" cy="410" r="6"/><circle cx="830" cy="480" r="4"/><circle cx="980" cy="400" r="5"/>
        </g>
    </svg>

    @foreach(['far', 'near'] as $layer)
        <div class="eco-layer eco-layer-{{ $layer }}">
            @foreach($icons as [$name, $x, $y, $size, $tone, $dur, $delay, $blur, $l, $hide])
                @if($l === $layer)
                    <span class="eco-ic tone-{{ $tone }} {{ $hide ? 'hide-sm' : '' }}"
                          style="left:{{ $x }}%;top:{{ $y }}%;width:{{ $size }}px;height:{{ $size }}px;--dur:{{ $dur }}s;--delay:-{{ $delay * 3 }}s;--blur:{{ $blur }}px">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round">{!! $svg[$name] !!}</svg>
                    </span>
                @endif
            @endforeach
        </div>
    @endforeach
</div>
