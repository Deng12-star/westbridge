@props([
    'eyebrow',
    'title',
    'lede',
    'items' => [],
    'cta',
    'href',
    'mirrored' => false,
])

{{-- Blocks 04 and 05. Alternating image/content split. --}}
<section class="bg-white">
    <div class="wb-container py-16 md:py-24">
        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">

            {{-- Visual --}}
            <div @class(['lg:order-2' => $mirrored])>
                <div class="relative overflow-hidden rounded-sm border border-paper-300 bg-paper-100">
                    <div class="aspect-4/3 w-full max-w-full">
                        {{ $visual }}
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div @class(['lg:order-1' => $mirrored])>
                <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">{{ $eyebrow }}</p>
                <h2 class="mt-3 text-h2">{{ $title }}</h2>
                <p class="mt-4 text-paper-600 wb-prose">{{ $lede }}</p>

                @if (filled($items))
                    <ul class="mt-6 grid gap-x-6 gap-y-2.5 sm:grid-cols-2">
                        @foreach ($items as $item)
                            <li class="flex items-start gap-2.5 text-small text-paper-700">
                                <svg class="mt-1 h-3.5 w-3.5 flex-shrink-0 text-lime-600" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                    <path d="M2.5 7.5l3 3 6-6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                <a wire:navigate href="{{ $href }}" class="mt-8 inline-flex">
                    <x-ui.button>{{ $cta }}</x-ui.button>
                </a>
            </div>
        </div>
    </div>
</section>
