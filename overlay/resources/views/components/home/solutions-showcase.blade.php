{{--
    Solutions showcase: the dark section with alternating panels, as on the
    reference site. Written as capabilities ("systems we build"), not as a list
    of delivered products - there are no client claims here to defend.
--}}
<section class="relative overflow-hidden bg-navy-800 text-white">
    <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image:linear-gradient(to right,#fff 1px,transparent 1px),linear-gradient(to bottom,#fff 1px,transparent 1px);background-size:56px 56px" aria-hidden="true"></div>

    <div class="wb-container relative py-20 md:py-28">
        <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <div class="max-w-2xl">
                <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400">Our Solutions</p>
                <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] text-white md:text-[2.5rem]">Systems we build, install and support</h2>
            </div>
            <a wire:navigate href="{{ route('services.index') }}" class="group inline-flex flex-shrink-0 items-center gap-2 font-display text-small font-semibold text-lime-400 hover:text-lime-300">
                All solutions
                <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
            </a>
        </div>

        <div class="mt-16 grid gap-20 lg:gap-28" data-no-stagger>
            @foreach (config('westbridge.home.showcase') as $i => $item)
                <div class="wb-showcase grid items-center gap-10 lg:grid-cols-2 lg:gap-16" data-reveal-item>
                    <div @class(['lg:order-2' => $i % 2 === 1])>
                        <x-home.mock-panel :type="$item['mock']" />
                    </div>

                    <div @class(['lg:order-1' => $i % 2 === 1])>
                        <p class="font-mono text-small text-lime-400">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }} / {{ $item['eyebrow'] }}</p>
                        <h3 class="mt-3 text-[1.75rem] font-bold leading-tight text-white md:text-[2rem]">{{ $item['title'] }}</h3>
                        <p class="mt-4 max-w-lg leading-relaxed text-navy-200">{{ $item['body'] }}</p>

                        <ul class="mt-7 grid max-w-md grid-cols-2 gap-3">
                            @foreach ($item['points'] as $point)
                                <li class="flex items-center gap-2.5 text-small text-navy-100">
                                    <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-lime-500/15 text-lime-400">
                                        <x-icons.wb name="check" class="h-3 w-3" />
                                    </span>
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>

                        <a wire:navigate href="{{ $item['url'] }}" class="group mt-9 inline-flex items-center gap-2 border-b border-lime-500/40 pb-1 font-display text-small font-semibold text-white transition-colors hover:border-lime-400 hover:text-lime-400">
                            Learn more
                            <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
