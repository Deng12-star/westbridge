<x-layouts.app :title="$page->meta_title ?? 'Services'" :description="$page->meta_description">

    <x-page.hero
        eyebrow="Services"
        :title="$page->heading ?? 'Everything from the software to the signal.'"
        :lede="$page->lede ?? 'Four areas of work, delivered by one team. Most engagements draw on more than one of them.'"
    />

    <section class="bg-white">
        <div class="wb-container py-16 md:py-24">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($businessAreas as $area)
                    <a wire:navigate href="{{ $area['url'] }}" class="wb-lift group flex flex-col gap-3 rounded-sm border border-paper-300 bg-white p-6 hover:border-navy-300">
                        <span class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">{{ $area['eyebrow'] }}</span>
                        <h2 class="text-h3">{{ $area['title'] }}</h2>
                        <p class="text-small text-paper-600">{{ $area['blurb'] }}</p>
                        <span class="mt-auto inline-flex items-center gap-1.5 pt-3 font-display text-small font-semibold text-navy-700">
                            {{ $area['cta'] }}
                            <svg class="h-3.5 w-3.5 transition-transform duration-150 group-hover:translate-x-0.5" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h9M8.5 4.5L12 8l-3.5 3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-paper-100">
        <div class="wb-container py-16 md:py-24">
            <x-ui.section-header
                eyebrow="Service lines"
                title="Where to start."
                lede="Each of these has its own page with the full scope and how we work."
            />

            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($solutions as $solution)
                    <a wire:navigate href="{{ $solution['url'] }}" class="wb-lift group flex flex-col gap-4 rounded-sm border border-paper-300 bg-white p-6 hover:border-navy-300">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xs bg-navy-700 text-lime-400">
                            {!! $solution['icon'] !!}
                        </span>
                        <h3 class="text-h3">{{ $solution['title'] }}</h3>
                        <p class="text-small text-paper-600">{{ $solution['blurb'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

</x-layouts.app>
