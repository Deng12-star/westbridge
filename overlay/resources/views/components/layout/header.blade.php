@props(['overlay' => false])

{{--
    Site header.

    overlay = true  (homepage): floats transparent over the dark hero with white
                    text and the reverse logo, then turns solid white once the
                    visitor scrolls or opens a menu.
    overlay = false (every other page): solid white and sticky from the start.

    Mega menus on Solutions and Shop only; every other item is a plain link.
--}}
<header
    x-data="{ scrolled: false, open: null, drawer: false, overlay: @js((bool) $overlay) }"
    x-init="scrolled = window.scrollY > 24"
    @scroll.window="scrolled = window.scrollY > 24"
    @keydown.escape.window="open = null; drawer = false"
    @class([
        'top-0 inset-x-0 z-50 border-b transition-[background-color,border-color,box-shadow] duration-300',
        'fixed' => $overlay,
        'sticky bg-white' => ! $overlay,
    ])
    :class="{
        'bg-white border-paper-300 shadow-[var(--shadow-header)]': scrolled || (overlay && open),
        'border-transparent': !scrolled && !(overlay && open),
        'bg-transparent': overlay && !scrolled && !open,
    }"
>
    <div class="wb-container">
        <div
            class="flex items-center justify-between gap-6 transition-[height] duration-200 ease-[var(--ease-brand)]"
            :class="scrolled ? 'h-16' : 'h-20'"
        >
            {{-- Logo: reverse (white) over the hero, primary once solid --}}
            <a wire:navigate href="{{ route('home') }}" class="relative flex-shrink-0" aria-label="WestBridge Technologies - home">
                <span
                    class="block h-14 transition-[height] duration-200 ease-[var(--ease-brand)]"
                    :class="scrolled ? '!h-10' : ''"
                >
                    @if ($overlay)
                        <x-brand.logo variant="reverse" class="h-full transition-opacity duration-300" ::class="(scrolled || open) ? 'opacity-0' : 'opacity-100'" />
                        <x-brand.logo class="absolute inset-0 h-full transition-opacity duration-300" ::class="(scrolled || open) ? 'opacity-100' : 'opacity-0'" alt="" aria-hidden="true" />
                    @else
                        <x-brand.logo class="h-full" />
                    @endif
                </span>
            </a>

            {{-- Desktop navigation --}}
            <nav
                class="hidden lg:flex items-center gap-1 transition-colors duration-300"
                :class="(overlay && !scrolled && !open) ? 'wb-nav-light' : ''"
                aria-label="Main"
            >
                <x-layout.nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-layout.nav-link>
                <x-layout.nav-link :href="route('about')" :active="request()->routeIs('about')">About</x-layout.nav-link>

                {{-- Solutions mega menu --}}
                <div class="relative" @mouseenter="open = 'solutions'" @mouseleave="open = null">
                    <button
                        type="button"
                        @click="open = open === 'solutions' ? null : 'solutions'"
                        :aria-expanded="open === 'solutions' ? 'true' : 'false'"
                        aria-controls="menu-solutions"
                        class="wb-navlink flex items-center gap-1.5 px-3 py-2 font-display text-[15px] font-medium transition-colors"
                        data-active="{{ request()->routeIs('services.*') ? 'true' : 'false' }}"
                        :data-active="open === 'solutions' ? 'true' : '{{ request()->routeIs('services.*') ? 'true' : 'false' }}'"
                    >
                        Solutions
                        <svg class="h-3.5 w-3.5 transition-transform" :class="open === 'solutions' && 'rotate-180'" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        id="menu-solutions"
                        x-show="open === 'solutions'"
                        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        x-cloak
                        class="absolute left-1/2 top-full z-50 w-[560px] -translate-x-1/2 pt-3"
                    >
                        <div class="rounded-sm border border-paper-300 bg-white p-2 shadow-[var(--shadow-overlay)]">
                            <div class="grid grid-cols-1 gap-1">
                                @foreach ($solutions as $item)
                                    <a wire:navigate href="{{ $item['url'] }}" class="group flex items-start gap-3 rounded-xs p-3 transition-colors hover:bg-paper-100">
                                        <span class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xs bg-navy-700 text-lime-400 transition-colors group-hover:bg-lime-500 group-hover:text-navy-800">
                                            {!! $item['icon'] !!}
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block font-display text-[15px] font-semibold text-navy-700">{{ $item['title'] }}</span>
                                            <span class="block text-small text-paper-600">{{ $item['blurb'] }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shop mega menu --}}
                <div class="relative" @mouseenter="open = 'shop'" @mouseleave="open = null">
                    <button
                        type="button"
                        @click="open = open === 'shop' ? null : 'shop'"
                        :aria-expanded="open === 'shop' ? 'true' : 'false'"
                        aria-controls="menu-shop"
                        class="wb-navlink flex items-center gap-1.5 px-3 py-2 font-display text-[15px] font-medium transition-colors"
                        data-active="{{ request()->routeIs('shop.*') ? 'true' : 'false' }}"
                        :data-active="open === 'shop' ? 'true' : '{{ request()->routeIs('shop.*') ? 'true' : 'false' }}'"
                    >
                        Shop
                        <svg class="h-3.5 w-3.5 transition-transform" :class="open === 'shop' && 'rotate-180'" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                            <path d="M3 4.5L6 7.5L9 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        id="menu-shop"
                        x-show="open === 'shop'"
                        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        x-cloak
                        class="absolute left-1/2 top-full z-50 w-[480px] -translate-x-1/2 pt-3"
                    >
                        <div class="rounded-sm border border-paper-300 bg-white p-2 shadow-[var(--shadow-overlay)]">
                            <div class="grid grid-cols-2 gap-1">
                                @foreach ($shopCategories as $cat)
                                    <a wire:navigate href="{{ $cat['url'] }}" class="flex items-center justify-between rounded-xs px-3 py-2.5 transition-colors hover:bg-paper-100">
                                        <span class="font-display text-[15px] font-medium text-navy-700">{{ $cat['title'] }}</span>
                                        @if (! is_null($cat['count']))
                                            <span class="font-mono text-xs text-paper-500">{{ $cat['count'] }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                            <a wire:navigate href="{{ route('shop.index') }}" class="mt-1 block rounded-xs bg-paper-100 px-3 py-2.5 text-center font-display text-small font-semibold text-navy-700 transition-colors hover:bg-paper-200">
                                Visit the full store
                            </a>
                        </div>
                    </div>
                </div>

                <x-layout.nav-link :href="route('portfolio.index')" :active="request()->routeIs('portfolio.*')">Portfolio</x-layout.nav-link>
                @if ($showInsights)
                    <x-layout.nav-link :href="route('news.index')" :active="request()->routeIs('news.*')">News</x-layout.nav-link>
                @endif
                <x-layout.nav-link :href="route('contact')" :active="request()->routeIs('contact')">Contact</x-layout.nav-link>
            </nav>

            {{-- Right side --}}
            <div class="flex items-center gap-2">
                <a wire:navigate href="{{ route('contact') }}" class="hidden sm:inline-flex">
                    <x-ui.button size="sm">
                        Contact Us
                        <svg class="h-3.5 w-3.5" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h9M8.5 4.5L12 8l-3.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </x-ui.button>
                </a>

                {{-- Mobile menu toggle --}}
                <button
                    type="button"
                    @click="drawer = true"
                    class="lg:hidden inline-flex h-11 w-11 items-center justify-center rounded-xs transition-colors"
                    :class="(overlay && !scrolled) ? 'text-white hover:bg-white/10' : 'text-navy-700 hover:bg-paper-100'"
                    aria-label="Open menu"
                    :aria-expanded="drawer ? 'true' : 'false'"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <x-layout.mobile-drawer :solutions="$solutions" :shop-categories="$shopCategories" :show-insights="$showInsights" />
</header>
