<x-layouts.app :title="$page->meta_title ?? 'About Us'" :description="$page->meta_description">

    <x-page.hero
        eyebrow="About WestBridge"
        :title="$page->heading ?? 'A technology partner, not a supplier.'"
        :lede="$page->lede"
    />

    {{-- Who we are / What we do --}}
    <section class="bg-white">
        <div class="wb-container py-16 md:py-24">
            <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h2 class="text-h2">Who we are</h2>
                    <div class="mt-4 grid gap-4 text-paper-700 wb-prose">
                        {!! $page->block('who_we_are') !!}
                    </div>
                </div>
                <div>
                    <h2 class="text-h2">What we do</h2>
                    <div class="mt-4 grid gap-4 text-paper-700 wb-prose">
                        {!! $page->block('what_we_do') !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Mission & vision --}}
    <section class="bg-navy-700">
        <div class="wb-container py-16 md:py-20">
            <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400">Our mission</p>
                    <p class="mt-4 text-xl leading-relaxed text-white wb-prose">{{ $page->block('mission') }}</p>
                </div>
                <div>
                    <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400">Our vision</p>
                    <p class="mt-4 text-xl leading-relaxed text-white wb-prose">{{ $page->block('vision') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Team - managed in Admin > Team; hidden until someone is added --}}
    @if ($team->isNotEmpty())
        <section id="team" class="scroll-mt-24 bg-paper-100">
            <div class="wb-container py-16 md:py-24">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">Our team</p>
                    <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] md:text-[2.5rem]">The people behind WestBridge</h2>
                </div>

                <div @class([
                    'mx-auto mt-12 grid gap-6 sm:grid-cols-2',
                    'lg:grid-cols-4' => $team->count() >= 4,
                    'lg:grid-cols-3 max-w-5xl' => $team->count() === 3,
                    'max-w-3xl' => $team->count() <= 2,
                ])>
                    @foreach ($team as $member)
                        <div data-reveal-item class="group flex flex-col items-center rounded-sm border border-paper-300 bg-white p-8 text-center transition-all duration-300 hover:-translate-y-1 hover:border-navy-300 hover:shadow-[0_20px_40px_-24px_rgba(28,43,79,0.45)]">
                            <div class="relative">
                                <span class="absolute -inset-1.5 rounded-full bg-gradient-to-br from-lime-500 to-navy-500 opacity-0 transition-opacity duration-300 group-hover:opacity-100" aria-hidden="true"></span>
                                <span class="relative flex h-32 w-32 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-navy-700 font-display text-3xl font-bold text-lime-400 shadow-[0_10px_30px_-12px_rgba(28,43,79,0.5)]">
                                    @if ($member->photoUrl())
                                        <img src="{{ $member->photoUrl() }}" alt="{{ $member->name }}" loading="lazy" decoding="async" width="256" height="256" class="h-full w-full object-cover">
                                    @else
                                        <span aria-hidden="true">{{ $member->initials() }}</span>
                                    @endif
                                </span>
                            </div>
                            <h3 class="mt-6 font-display text-[1.15rem] font-bold text-navy-700">{{ $member->name }}</h3>
                            <p class="mt-1 font-display text-small font-semibold uppercase tracking-[0.08em] text-lime-700">{{ $member->position }}</p>
                            @if ($member->bio)
                                <p class="mt-4 text-small leading-relaxed text-paper-600">{{ $member->bio }}</p>
                            @endif
                            @if ($member->linkedin_url)
                                <a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener" class="mt-5 inline-flex h-9 w-9 items-center justify-center rounded-full border border-paper-300 text-navy-700 transition-colors hover:border-lime-500 hover:bg-lime-500 hover:text-navy-900" aria-label="{{ $member->name }} on LinkedIn">
                                    <x-icons.linkedin class="h-4 w-4" />
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Values --}}
    <section class="bg-white">
        <div class="wb-container py-16 md:py-24">
            <x-ui.section-header eyebrow="Our values" title="What we hold ourselves to." />

            <div class="mt-10 grid gap-x-10 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($page->block('values', []) as $value)
                    <div class="border-t-2 border-lime-500 pt-4">
                        <h3 class="text-h3">{{ $value['title'] }}</h3>
                        <p class="mt-2 text-small text-paper-600">{{ $value['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Approach --}}
    <section class="bg-paper-100">
        <div class="wb-container py-16 md:py-24">
            <x-ui.section-header
                eyebrow="Our approach"
                title="How we work with you."
                lede="The same sequence on every engagement, whether it is a payroll system or a WiFi installation."
            />

            <ol class="mt-10 grid gap-px overflow-hidden rounded-sm border border-paper-300 bg-paper-300 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($page->block('approach', []) as $index => $stage)
                    <li class="bg-white p-6">
                        <span class="font-mono text-small text-lime-700">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-2 text-h3">{{ $stage['title'] }}</h3>
                        <p class="mt-2 text-small text-paper-600">{{ $stage['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Story --}}
    @if ($page->block('story'))
        <section class="bg-white">
            <div class="wb-container py-16 md:py-24">
                <div class="mx-auto max-w-3xl">
                    <x-ui.section-header eyebrow="Our story" title="How WestBridge started." />
                    <div class="mt-6 grid gap-4 text-paper-700">
                        {!! $page->block('story') !!}
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Why technology matters --}}
    @if ($page->block('why_technology'))
        <section class="bg-paper-100">
            <div class="wb-container py-16 md:py-24">
                <div class="mx-auto max-w-3xl">
                    <x-ui.section-header eyebrow="Why technology matters" title="The case for building this properly." />
                    <div class="mt-6 grid gap-4 text-paper-700">
                        {!! $page->block('why_technology') !!}
                    </div>
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>
