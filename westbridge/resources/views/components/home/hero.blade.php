{{--
    Homepage hero, in the QT Global shape: tall, dark, full-bleed, with a
    living network behind the headline.

    The network is an original canvas drawing - nodes drifting slowly, linking
    when they come close, lime signals among the navy - and it answers the
    pointer: move across it and nearby nodes reach towards you. It is the
    brand line made visible: connecting ideas.

    Without JavaScript, or with reduced motion requested, the canvas simply
    draws one still frame; the headline never depends on it.
--}}
<section class="wb-hero relative isolate overflow-hidden bg-navy-900 text-white">
    <canvas data-wb-network class="absolute inset-0 -z-10 h-full w-full" aria-hidden="true"></canvas>

    {{-- Readability: darken behind the copy, let the network breathe on the right --}}
    <div class="wb-hero-shade pointer-events-none absolute inset-0 -z-10" aria-hidden="true"></div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-40 bg-gradient-to-b from-transparent to-navy-900" aria-hidden="true"></div>

    <div class="wb-container">
        <div class="flex min-h-[640px] flex-col justify-center pb-24 pt-36 lg:min-h-[780px] lg:pb-32 lg:pt-44">
            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400 backdrop-blur">
                    <span class="relative flex h-2 w-2">
                        <span class="wb-ping absolute inline-flex h-full w-full rounded-full bg-lime-400"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-lime-500"></span>
                    </span>
                    {{ setting('company.tagline', 'Connecting Ideas. Building Tomorrow.') }}
                </p>

                <h1 class="mt-6 text-[2.5rem] font-bold leading-[1.05] tracking-[-0.025em] text-white sm:text-[3.25rem] lg:text-[4.25rem]">
                    Technology That <span class="wb-hero-accent">Connects Ideas</span> to Possibilities.
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-relaxed text-navy-200">
                    WestBridge Technologies builds software, delivers IT infrastructure and connectivity,
                    and supplies technology products - connecting businesses, institutions and
                    organisations across South Sudan through technology they can rely on.
                </p>

                <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                    <a href="#services" class="group">
                        <x-ui.button size="lg" class="w-full justify-center sm:w-auto">
                            Explore More
                            <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                        </x-ui.button>
                    </a>
                    <a wire:navigate href="{{ route('contact') }}">
                        <x-ui.button size="lg" variant="ghost-light" class="w-full justify-center sm:w-auto">Contact Us</x-ui.button>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll cue --}}
    <a href="#services" class="wb-scroll-cue absolute bottom-7 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-2 text-navy-300 transition-colors hover:text-lime-400 md:flex" aria-label="Scroll to services">
        <span class="font-display text-[11px] font-semibold uppercase tracking-[0.22em]">Scroll</span>
        <span class="flex h-9 w-5 justify-center rounded-full border border-current pt-1.5">
            <span class="wb-scroll-dot h-1.5 w-1 rounded-full bg-current"></span>
        </span>
    </a>
</section>
