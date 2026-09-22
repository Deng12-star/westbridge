@php
    $meta = collect([
        'Client' => $project['client'],
        'Location' => $project['location'],
        'Sector' => $project['industry'],
        'Service' => $project['service'],
        'Year' => $project['year'],
        'Status' => $project['status'],
    ])->filter();
@endphp

<x-layouts.app :title="$project['title']" :description="$project['summary']" :og-image="$project['image']">

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-navy-900 text-white">
        <div class="pointer-events-none absolute -right-40 -top-40 h-[520px] w-[520px] rounded-full bg-lime-500/10 blur-3xl" aria-hidden="true"></div>

        <div class="wb-container relative grid items-center gap-12 py-14 md:py-20 lg:grid-cols-[1.05fr_1fr] lg:gap-16">
            <div>
                <nav aria-label="Breadcrumb" class="text-xs text-navy-300">
                    <a wire:navigate href="{{ route('home') }}" class="transition-colors hover:text-lime-400">Home</a>
                    <span class="mx-1.5 text-navy-500">/</span>
                    <a wire:navigate href="{{ route('portfolio.index') }}" class="transition-colors hover:text-lime-400">Portfolio</a>
                    <span class="mx-1.5 text-navy-500">/</span>
                    <span class="text-white" aria-current="page">{{ $project['title'] }}</span>
                </nav>

                @if ($project['category'])
                    <p class="mt-8 font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400">{{ $project['category'] }}</p>
                @endif
                <h1 class="mt-3 text-[2.25rem] font-bold leading-[1.1] tracking-[-0.02em] text-white md:text-[3rem]">{{ $project['title'] }}</h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-navy-200">{{ $project['summary'] }}</p>

                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($project['live_url'])
                        <a href="{{ $project['live_url'] }}" target="_blank" rel="noopener" class="group inline-flex items-center gap-2 rounded-xs bg-lime-500 px-6 py-3.5 font-display text-small font-semibold text-navy-900 transition-colors hover:bg-lime-400">
                            Visit the live system
                            <x-icons.wb name="arrow" class="h-4 w-4 -rotate-45 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                        </a>
                    @endif
                    <a wire:navigate href="{{ route('contact') }}" class="group inline-flex items-center gap-2 rounded-xs border border-white/25 px-6 py-3.5 font-display text-small font-semibold text-white transition-colors hover:border-lime-400 hover:text-lime-400">
                        Build something similar
                        <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </a>
                </div>
            </div>

            <div data-reveal-item>
                @if ($project['image'])
                    <div class="overflow-hidden rounded-sm border border-white/10 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.6)]">
                        <img src="{{ $project['image'] }}" alt="{{ $project['title'] }} screenshot" class="h-auto w-full" decoding="async">
                    </div>
                @else
                    <x-home.mock-panel :type="$project['mock']" :label="$project['domain']" />
                @endif
            </div>
        </div>
    </section>

    {{-- Facts --}}
    @if ($meta->isNotEmpty())
        <section class="border-b border-paper-200 bg-white">
            <div class="wb-container">
                <dl @class([
                    'grid grid-cols-2 divide-paper-200 md:divide-x',
                    'md:grid-cols-3' => $meta->count() <= 3,
                    'md:grid-cols-4' => $meta->count() === 4,
                    'md:grid-cols-5' => $meta->count() >= 5,
                ])>
                    @foreach ($meta as $label => $value)
                        <div class="py-6 md:px-6 md:first:pl-0">
                            <dt class="font-mono text-[11px] uppercase tracking-wider text-paper-500">{{ $label }}</dt>
                            <dd class="mt-1.5 font-display text-[15px] font-semibold text-navy-700">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    @endif

    {{-- Overview + scope --}}
    <section class="bg-white">
        <div class="wb-container grid gap-14 py-16 md:py-24 lg:grid-cols-[1.2fr_1fr] lg:gap-20">
            <div>
                <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">Overview</p>
                <h2 class="mt-3 text-h2">About the project</h2>
                <div class="mt-6 grid gap-5 text-paper-700 wb-prose">
                    @foreach ($project['overview'] as $paragraph)
                        <p class="leading-relaxed">{{ $paragraph }}</p>
                    @endforeach
                </div>
            </div>

            @if (filled($project['scope']))
                <div class="self-start rounded-sm border border-paper-300 bg-paper-100 p-8">
                    <h3 class="font-display text-h3 font-bold text-navy-700">What the system does</h3>
                    <span class="mt-3 block h-0.5 w-10 bg-lime-500" aria-hidden="true"></span>
                    <ul class="mt-6 grid gap-4">
                        @foreach ($project['scope'] as $item)
                            <li class="flex gap-3 text-paper-700">
                                <span class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-lime-500 text-navy-900">
                                    <x-icons.wb name="check" class="h-3.5 w-3.5" />
                                </span>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- More work --}}
    @if ($others->isNotEmpty())
        <section class="bg-paper-100">
            <div class="wb-container py-16 md:py-24">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <h2 class="text-h2">More projects</h2>
                    <a wire:navigate href="{{ route('portfolio.index') }}" class="group inline-flex items-center gap-2 font-display text-small font-semibold text-navy-700 hover:text-lime-700">
                        All projects
                        <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </a>
                </div>
                <div class="mt-10 grid gap-6 md:grid-cols-2">
                    @foreach ($others as $other)
                        <x-work.project-card :project="$other" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>
