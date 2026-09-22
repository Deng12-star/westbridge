@props(['name'])

{{-- One line-icon set for the whole site: 24px grid, 1.6 stroke, round caps. --}}
{{-- Default size only when the caller gives none: two height classes would fight. --}}
<svg {{ $attributes->merge(['class' => $attributes->has('class') ? '' : 'h-6 w-6']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('code')
            <path d="M8 7l-5 5 5 5M16 7l5 5-5 5M13.5 4l-3 16"/>
            @break
        @case('network')
            <rect x="9" y="3" width="6" height="5" rx="1"/><rect x="3" y="16" width="6" height="5" rx="1"/><rect x="15" y="16" width="6" height="5" rx="1"/><path d="M12 8v4M6 16v-4h12v4"/>
            @break
        @case('satellite')
            <path d="M5 19l5-5"/><path d="M13 5.5a7 7 0 015.5 5.5L10 19.5 4.5 14 13 5.5z"/><path d="M15.5 3a9 9 0 015.5 5.5"/>
            @break
        @case('device')
            <rect x="4" y="5" width="16" height="11" rx="1.5"/><path d="M2 19h20"/>
            @break
        @case('education')
            <path d="M12 4L2 9l10 5 10-5-10-5z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>
            @break
        @case('government')
            <path d="M3 21h18M5 21V10M9.5 21V10M14.5 21V10M19 21V10M2 10l10-6 10 6"/>
            @break
        @case('ngo')
            <path d="M12 20s-7-4.4-7-10a4 4 0 017-2.6A4 4 0 0119 10c0 5.6-7 10-7 10z"/>
            @break
        @case('finance')
            <rect x="3" y="6" width="18" height="13" rx="1.5"/><path d="M3 10h18M7 15h3"/>
            @break
        @case('retail')
            <path d="M4 8h16l-1 12H5L4 8z"/><path d="M9 8V6a3 3 0 016 0v2"/>
            @break
        @case('health')
            <path d="M12 21a9 9 0 100-18 9 9 0 000 18z"/><path d="M12 8v8M8 12h8"/>
            @break
        @case('arrow')
            <path d="M5 12h13M13 6l6 6-6 6"/>
            @break
        @case('check')
            <path d="M5 12.5l4.5 4.5L19 7.5"/>
            @break
        @case('pin')
            <path d="M12 21s7-6 7-11.5A7 7 0 005 9.5C5 15 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
            @break
        @case('phone')
            <path d="M5 4h4l1.5 4.5-2.3 1.5a11 11 0 005.8 5.8l1.5-2.3L20 15v4a1.5 1.5 0 01-1.6 1.5A16 16 0 013.5 5.6 1.5 1.5 0 015 4z"/>
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="M3.5 6.5L12 13l8.5-6.5"/>
            @break
        @case('spark')
            <path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5L18 18M6 18l2.5-2.5M15.5 8.5L18 6"/>
            @break
        @case('news')
            <rect x="3" y="4" width="15" height="16" rx="1.5"/><path d="M18 8h2a1 1 0 011 1v9a2 2 0 01-2 2H5"/><path d="M7 8h7M7 12h7M7 16h4"/>
            @break
        @case('team')
            <circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.3"/><path d="M16 14.2c2.8.4 5 2.8 5 5.8"/>
            @break
        @case('camera')
            <path d="M3 7.5l12-3.5 1.5 5L4.5 12.5z"/><path d="M16.5 9l3.5-1v5l-3 .8"/><path d="M8 11.5V16H4M4 14v4"/>
            @break
        @case('intercom')
            <rect x="6" y="2.5" width="12" height="19" rx="2"/><circle cx="12" cy="7.5" r="2"/><path d="M9.5 13h5M9.5 16h5M11 19h2"/>
            @break
        @case('shield')
            <path d="M12 3l7.5 3v5.5c0 4.6-3.2 8.4-7.5 9.5-4.3-1.1-7.5-4.9-7.5-9.5V6L12 3z"/><path d="M8.8 12l2.2 2.2 4.2-4.4"/>
            @break
        @default
            <circle cx="12" cy="12" r="8"/>
    @endswitch
</svg>
