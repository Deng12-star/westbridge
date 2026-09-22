@props(['variant' => 'compact'])

@php
    /*
     | Variants — all are crops of the official logo file, never redrawings.
     |
     | compact  the lockup WITHOUT the slogan line. Use anywhere the logo sits
     |          below ~64px: the slogan is set far smaller than the wordmark and
     |          turns to mush at header and footer sizes. This is the default.
     | primary  the full lockup including the slogan. Use at 96px and above —
     |          documents, letterheads, the OG image, print.
     | mark     the bridge symbol alone, for favicons and tight square spaces.
     */
    /*
     | Served from public/brand, NOT through Vite.
     |
     | Vite::asset() only resolves files that end up in the build manifest,
     | which means files imported by the bundled CSS or JS. A logo referenced
     | only from a Blade template never enters the manifest, so Vite::asset()
     | throws. These are static brand assets that want stable, predictable
     | URLs anyway - the same place the favicons live.
     */
    $file = match ($variant) {
        'primary' => 'logo-primary.png',
        'primary-reverse' => 'logo-reverse.png',
        'reverse' => 'logo-compact-reverse.png',
        'mark' => 'logo-mark.png',
        'mark-reverse' => 'logo-mark-reverse.png',
        default => 'logo-compact.png',
    };

    $src = asset('brand/'.$file);
@endphp

<img
    src="{{ $src }}"
    alt="{{ $attributes->get('alt', 'WestBridge Technologies — Connecting Ideas. Building Tomorrow.') }}"
    {{ $attributes->except('alt')->merge(['class' => 'w-auto']) }}
>
