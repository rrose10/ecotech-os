@php
    $paths = [
        'dashboard'   => '<rect x="3" y="3" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="2"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="2"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="2"/>',
        'employees'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.6 3.2-5.5 6.5-5.5s5.9 1.9 6.5 5.5"/><path d="M16 4.8a3.4 3.4 0 0 1 0 6.4M18 14.8c1.9.7 3.1 2.4 3.5 5.2"/>',
        'departments' => '<path d="M4 21V5a1.5 1.5 0 0 1 1.5-1.5h8A1.5 1.5 0 0 1 15 5v16"/><path d="M15 9h3.5A1.5 1.5 0 0 1 20 10.5V21"/><path d="M8 8h3M8 12h3M8 16h3M2.5 21h19"/>',
        'attendance'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.2 2"/>',
        'calendar'    => '<rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M8 3v4M16 3v4M3.5 10h17"/>',
        'leaves'      => '<path d="M7 3.5h7.5L19 8v11a1.5 1.5 0 0 1-1.5 1.5h-10A1.5 1.5 0 0 1 6 19V5A1.5 1.5 0 0 1 7 3.5Z"/><path d="M14 3.5V8h5M9 13h6M9 16.5h6"/>',
        'balance'     => '<path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/>',
        'tag'         => '<path d="M3.5 12.2V4.5a1 1 0 0 1 1-1h7.7a1 1 0 0 1 .7.3l7.6 7.6a1 1 0 0 1 0 1.4l-7.7 7.7a1 1 0 0 1-1.4 0l-7.6-7.6a1 1 0 0 1-.3-.7Z"/><circle cx="8.2" cy="8.2" r="1.3"/>',
        'expense'     => '<circle cx="12" cy="12" r="9"/><path d="M8.5 13.5 12 10l3.5 3.5"/>',
        'income'      => '<circle cx="12" cy="12" r="9"/><path d="M8.5 10.5 12 14l3.5-3.5"/>',
        'advance'     => '<path d="M3.5 7.5A2.5 2.5 0 0 1 6 5h11.5A1.5 1.5 0 0 1 19 6.5V8"/><path d="M3.5 7.5V17A2.5 2.5 0 0 0 6 19.5h12.5A1.5 1.5 0 0 0 20 18V9.5A1.5 1.5 0 0 0 18.5 8H6a2.5 2.5 0 0 1-2.5-.5Z"/><circle cx="16" cy="13.8" r="1.1"/>',
        'loan'        => '<rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19M6.5 15h4"/>',
        'payroll'     => '<rect x="2.5" y="6" width="19" height="12" rx="2.5"/><circle cx="12" cy="12" r="2.6"/><path d="M6 9.5v.01M18 14.5v.01"/>',
        'transfer'    => '<path d="M4 8h13M13.5 4.5 17 8l-3.5 3.5"/><path d="M20 16H7M10.5 12.5 7 16l3.5 3.5"/>',
        'bank'        => '<path d="M3 9.5 12 4l9 5.5M5 10v8M9.5 10v8M14.5 10v8M19 10v8M3 20.5h18"/>',
        'notice'      => '<path d="M4 10v4a1 1 0 0 0 1 1h2l8 4.5v-14.5L7 9H5a1 1 0 0 0-1 1Z"/><path d="M18.5 9.5a4 4 0 0 1 0 5"/>',
        'logs'        => '<path d="M5 4.5h14M5 9.5h14M5 14.5h9M5 19.5h6"/>',
        'assets'      => '<rect x="4.5" y="5" width="15" height="10.5" rx="2"/><path d="M2.5 19h19"/>',
        'inventory'   => '<path d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5v-9Z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/>',
        'reports'     => '<path d="M3.5 19.5h17"/><path d="m5 15 4-4.5 3.5 3L19 6.5"/>',
        'settings'    => '<path d="M4 7h9M17 7h3M4 17h3M11 17h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/>',
        'profile'     => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20c.8-4 3.7-6 7.5-6s6.7 2 7.5 6"/>',
        'bell'        => '<path d="M6 16.5V11a6 6 0 0 1 12 0v5.5l1.5 2h-15Z"/><path d="M10 20.5a2.2 2.2 0 0 0 4 0"/>',
        'home'        => '<path d="M3.5 11 12 4l8.5 7M6 9.5V20h12V9.5"/>',
    ];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
