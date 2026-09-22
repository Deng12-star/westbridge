{{-- About + counters, as on the reference site. The counters count up when they come into view. --}}
<section class="bg-white">
    <div class="wb-container py-20 md:py-28">
        <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-20">

            {{-- Visual --}}
            <div class="relative order-2 lg:order-1">
                <div class="relative overflow-hidden rounded-sm bg-navy-700 p-10 md:p-14">
                    <div class="pointer-events-none absolute inset-0 opacity-[0.08]" style="background-image:radial-gradient(#fff 1px,transparent 1px);background-size:22px 22px" aria-hidden="true"></div>
                    <x-brand.logo variant="mark-reverse" class="relative mx-auto h-40 md:h-52" alt="" />
                    <p class="relative mt-8 text-center font-display text-xs font-semibold uppercase tracking-[0.3em] text-lime-400">Connecting Ideas. Building Tomorrow.</p>
                </div>

                {{-- floating chip --}}
                <div class="wb-float absolute -bottom-6 -right-3 flex items-center gap-3 rounded-sm bg-lime-500 px-5 py-4 text-navy-900 shadow-[var(--shadow-overlay)] md:-right-6">
                    <x-icons.wb name="pin" class="h-6 w-6" />
                    <div>
                        <p class="font-display text-small font-bold leading-tight">Based in Juba</p>
                        <p class="text-xs">Serving South Sudan & East Africa</p>
                    </div>
                </div>
            </div>

            {{-- Copy --}}
            <div class="order-1 lg:order-2">
                <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">About WestBridge</p>
                <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] md:text-[2.5rem]">A technology partner, not just a supplier</h2>
                <p class="mt-5 leading-relaxed text-paper-600">
                    WestBridge Technologies works with businesses, institutions, government entities and
                    organisations - building the systems they run on, installing the infrastructure those
                    systems need, and supplying the equipment on the desk. Software, connectivity and
                    hardware from one team, so responsibility never falls between suppliers.
                </p>

                <div class="mt-9 grid gap-6 sm:grid-cols-2">
                    @foreach ([
                        ['spark', 'Built for local conditions', 'Intermittent power, variable bandwidth, mixed equipment.'],
                        ['network', 'End-to-end delivery', 'From the first line of code to the last cable run.'],
                    ] as [$icon, $title, $body])
                        <div class="flex gap-4">
                            <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-sm bg-lime-500/15 text-lime-700">
                                <x-icons.wb :name="$icon" class="h-6 w-6" />
                            </span>
                            <div>
                                <h3 class="font-display text-[1.05rem] font-bold text-navy-700">{{ $title }}</h3>
                                <p class="mt-1 text-small text-paper-600">{{ $body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <a wire:navigate href="{{ route('about') }}" class="group mt-10 inline-flex">
                    <x-ui.button variant="secondary">
                        More about us
                        <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </x-ui.button>
                </a>
            </div>
        </div>

        {{-- Counters --}}
        <div class="mt-24 grid grid-cols-2 gap-px overflow-hidden rounded-sm border border-paper-300 bg-paper-300 lg:grid-cols-4">
            @foreach (config('westbridge.home.stats') as $stat)
                <div class="bg-white px-6 py-10 text-center">
                    <p class="font-display text-[2.75rem] font-bold leading-none tracking-[-0.03em] text-navy-700 md:text-[3.25rem]">
                        <span data-count="{{ $stat['value'] }}">{{ $stat['value'] }}</span><span class="text-lime-600">{{ $stat['suffix'] }}</span>
                    </p>
                    <p class="mx-auto mt-3 max-w-[14rem] text-small text-paper-600">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
