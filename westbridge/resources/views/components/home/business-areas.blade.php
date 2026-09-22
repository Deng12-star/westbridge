{{-- Block 03. Appears BEFORE the store on purpose: it is what separates
     WestBridge from a computer shop. --}}
<section class="bg-paper-100">
    <div class="wb-container py-16 md:py-24">
        <x-ui.section-header
            eyebrow="Business areas"
            title="One partner, from the software to the cable."
            lede="Most projects need more than one of these. Running them under one roof means nobody is waiting on a third party to finish."
        />

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (config('westbridge.business_areas') as $area)
                <a wire:navigate href="{{ $area['url'] }}" class="wb-lift group flex flex-col gap-3 rounded-sm border border-paper-300 bg-white p-6 hover:border-navy-300">
                    <span class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">{{ $area['eyebrow'] }}</span>
                    <h3 class="text-h3">{{ $area['title'] }}</h3>
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
