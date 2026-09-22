@props(['solutions' => [], 'shopCategories' => [], 'showInsights' => false])

{{-- Full-height drawer. Contact is pinned to the base so the primary
     action is always in thumb reach. --}}
<div
    x-show="drawer"
    x-cloak
    class="lg:hidden fixed inset-0 z-50"
    role="dialog"
    aria-modal="true"
    aria-label="Menu"
>
    <div
        x-show="drawer"
        x-transition.opacity.duration.200ms
        @click="drawer = false"
        class="absolute inset-0 bg-navy-900/50"
    ></div>

    <div
        x-show="drawer"
        x-transition:enter="transition ease-[var(--ease-brand)] duration-250"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-[var(--ease-brand)] duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute inset-y-0 right-0 flex w-[88%] max-w-sm flex-col bg-white shadow-[var(--shadow-overlay)]"
    >
        <div class="flex h-20 flex-shrink-0 items-center justify-between border-b border-paper-200 px-5">
            <x-brand.logo class="h-12 w-auto" />
            <button
                type="button"
                @click="drawer = false"
                class="inline-flex h-11 w-11 items-center justify-center rounded-xs text-navy-700 transition-colors hover:bg-paper-100"
                aria-label="Close menu"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto overscroll-contain px-5 py-4" aria-label="Mobile">
            <a wire:navigate href="{{ route('home') }}" class="block border-b border-paper-200 py-3.5 font-display text-lg font-medium text-navy-700">Home</a>
            <a wire:navigate href="{{ route('about') }}" class="block border-b border-paper-200 py-3.5 font-display text-lg font-medium text-navy-700">About</a>

            <div class="border-b border-paper-200 py-3.5">
                <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-paper-500">Solutions</p>
                <div class="mt-2 grid gap-1">
                    @foreach ($solutions as $item)
                        <a wire:navigate href="{{ $item['url'] }}" class="block py-2 font-display text-base font-medium text-navy-700">{{ $item['title'] }}</a>
                    @endforeach
                </div>
            </div>

            <div class="border-b border-paper-200 py-3.5">
                <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-paper-500">Shop</p>
                <div class="mt-2 grid grid-cols-2 gap-1">
                    @foreach ($shopCategories as $cat)
                        <a wire:navigate href="{{ $cat['url'] }}" class="block py-2 font-display text-base font-medium text-navy-700">{{ $cat['title'] }}</a>
                    @endforeach
                </div>
            </div>

            <a wire:navigate href="{{ route('portfolio.index') }}" class="block border-b border-paper-200 py-3.5 font-display text-lg font-medium text-navy-700">Portfolio</a>
            @if ($showInsights)
                <a wire:navigate href="{{ route('news.index') }}" class="block border-b border-paper-200 py-3.5 font-display text-lg font-medium text-navy-700">News</a>
            @endif
            <a wire:navigate href="{{ route('contact') }}" class="block border-b border-paper-200 py-3.5 font-display text-lg font-medium text-navy-700">Contact</a>

            @if (setting('contact.phone'))
                <a href="tel:{{ setting('contact.phone') }}" class="mt-5 flex items-center gap-2.5 font-mono text-small text-paper-600">
                    <svg class="h-4 w-4 text-lime-700" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M3 3.5h3l1 3-1.5 1a8 8 0 004 4l1-1.5 3 1v3a1 1 0 01-1 1A11 11 0 012 4.5a1 1 0 011-1z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                    </svg>
                    {{ setting('contact.phone') }}
                </a>
            @endif
        </nav>

        <div class="flex-shrink-0 border-t border-paper-200 p-5">
            <a wire:navigate href="{{ route('contact') }}" class="block">
                <x-ui.button class="w-full justify-center">Contact Us</x-ui.button>
            </a>
        </div>
    </div>
</div>
