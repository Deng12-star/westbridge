{{-- Block 06. The one dark band on the page — it makes Starlink read as a
     distinct offer. Copy stays inside what decision D3 confirms: we sell,
     install, configure and support the hardware. No reseller claim, no speeds. --}}
<section class="bg-navy-700">
    <div class="wb-container py-16 md:py-24">
        <div class="grid gap-10 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="max-w-2xl">
                <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400">Starlink solutions</p>
                <h2 class="mt-3 text-h2 text-white">High-Speed Connectivity Where You Need It.</h2>
                <p class="mt-4 text-navy-200 wb-prose">
                    Satellite connectivity opens up sites that fibre and fixed wireless do not reach. We supply the
                    equipment, install it, configure the network behind it, and support it afterwards — so a field
                    office, a clinic or a compound gets a working connection rather than a box.
                </p>
            </div>

            <a wire:navigate href="{{ route('quote', ['service' => 'starlink']) }}" class="flex-shrink-0">
                <x-ui.button size="lg">Get Starlink</x-ui.button>
            </a>
        </div>

        <div class="mt-10 grid gap-px overflow-hidden rounded-sm bg-navy-600 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ([
                ['Equipment', 'Dish, router and mounting hardware supplied.'],
                ['Installation', 'Site survey, mounting and cabling done properly.'],
                ['Configuration', 'Connected to your existing network and devices.'],
                ['Optimisation', 'Placement and coverage tuned for the building.'],
                ['Support', 'Ongoing help after the installation is finished.'],
            ] as [$title, $blurb])
                <div class="bg-navy-700 p-5">
                    <h3 class="font-display text-[15px] font-semibold text-white">{{ $title }}</h3>
                    <p class="mt-1.5 text-small text-navy-300">{{ $blurb }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
