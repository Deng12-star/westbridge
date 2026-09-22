{{-- Services: the four business lines. Cards invert to navy on hover. --}}
<section id="services" class="scroll-mt-20 bg-white">
    <div class="wb-container py-20 md:py-28">
        <div class="mx-auto max-w-2xl text-center">
            <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">Our Services</p>
            <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] md:text-[2.5rem]">Services built for growth</h2>
            <p class="mt-4 text-paper-600">Four service lines, one team - so a single partner can take your project from the software through to the hardware and connectivity that runs it.</p>
        </div>

        {{-- Five cards: three on the first row, two wider ones on the second. --}}
        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-6">
            @foreach (config('westbridge.home.services') as $service)
                <a wire:navigate href="{{ $service['url'] }}" @class([
                    'group relative flex flex-col overflow-hidden rounded-sm border border-paper-300 bg-white p-8 transition-all duration-300 ease-[var(--ease-brand)] hover:-translate-y-1.5 hover:border-navy-700 hover:bg-navy-700 hover:shadow-[0_24px_48px_-24px_rgba(28,43,79,0.55)]',
                    'lg:col-span-2' => $loop->count !== 5 || $loop->index < 3,
                    'lg:col-span-3' => $loop->count === 5 && $loop->index >= 3,
                ])>
                    {{-- corner accent that grows on hover --}}
                    <span class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-lime-500/10 transition-transform duration-500 ease-[var(--ease-brand)] group-hover:scale-[2.6] group-hover:bg-lime-500/15" aria-hidden="true"></span>

                    <span class="relative flex h-14 w-14 items-center justify-center rounded-sm bg-navy-50 text-navy-700 transition-all duration-300 group-hover:rotate-[-6deg] group-hover:bg-lime-500 group-hover:text-navy-900">
                        <x-icons.wb :name="$service['icon']" class="h-7 w-7" />
                    </span>

                    <h3 class="relative mt-7 text-h3 font-bold text-navy-700 transition-colors duration-300 group-hover:text-white">{{ $service['title'] }}</h3>
                    <p class="relative mt-3 text-small leading-relaxed text-paper-600 transition-colors duration-300 group-hover:text-navy-200">{{ $service['body'] }}</p>

                    <span class="relative mt-auto inline-flex items-center gap-2 pt-7 font-display text-small font-semibold text-navy-700 transition-colors duration-300 group-hover:text-lime-400">
                        Learn more
                        <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
