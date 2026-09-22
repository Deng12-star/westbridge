@props(['title' => null, 'description' => null, 'ogImage' => null, 'overlayHeader' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-pt-24">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{--
        This rule is inline on purpose.

        x-cloak marks elements Alpine will hide the moment it initialises -
        the mega menus and the mobile drawer. If the rule only lived in the
        compiled stylesheet it would arrive too late on a slow connection,
        and those panels would paint over the page first. Inline, it applies
        before anything else is fetched. It is three lines; it earns them.
    --}}
    <style>[x-cloak]{display:none !important}</style>

    @include('partials.head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{--
        Alpine ships inside Livewire's script bundle. Livewire only injects
        that bundle automatically when it decides the page needs it, and the
        homepage has no Livewire component on it - so Alpine was never loaded
        there and every x-show in the header did nothing.

        Declaring the assets explicitly loads them on every page, which is what
        a site-wide header requires. Livewire sees the manual directives and
        skips its own injection, so nothing is loaded twice.
    --}}
    @livewireStyles

    @stack('head')
</head>
<body class="min-h-screen bg-white text-paper-900 antialiased">
    <a href="#main" class="wb-skip">Skip to content</a>

    <x-layout.header :overlay="$overlayHeader" />

    <main id="main">
        {{ $slot }}
    </main>

    <x-layout.cta-band />
    <x-layout.footer />
    <x-layout.whatsapp-button />

    @livewireScripts
    @stack('scripts')
</body>
</html>
