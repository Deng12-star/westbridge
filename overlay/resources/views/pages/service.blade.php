@php
    $wa = whatsapp_url('Hello WestBridge, I would like to discuss '.$service['title'].'.');
@endphp

<x-layouts.app :title="$service['title']" :description="$service['intro']">

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-navy-900 text-white">
        <div class="pointer-events-none absolute -right-40 -top-40 h-[520px] w-[520px] rounded-full bg-lime-500/10 blur-3xl" aria-hidden="true"></div>
        <div class="wb-container relative grid items-center gap-12 py-14 md:py-20 lg:grid-cols-[1.05fr_1fr] lg:gap-16">
            <div>
                <nav aria-label="Breadcrumb" class="text-xs text-navy-300">
                    <a wire:navigate href="{{ route('home') }}" class="hover:text-lime-400">Home</a>
                    <span class="mx-1.5 text-navy-500">/</span>
                    <a wire:navigate href="{{ route('services.index') }}" class="hover:text-lime-400">Services</a>
                    <span class="mx-1.5 text-navy-500">/</span>
                    <span class="text-white" aria-current="page">{{ $service['title'] }}</span>
                </nav>

                <span class="mt-8 flex h-14 w-14 items-center justify-center rounded-sm bg-lime-500 text-navy-900">
                    <x-icons.wb :name="$service['icon']" class="h-7 w-7" />
                </span>
                <p class="mt-6 font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400">{{ $service['eyebrow'] }}</p>
                <h1 class="mt-3 text-[2.25rem] font-bold leading-[1.1] tracking-[-0.02em] text-white md:text-[3rem]">{{ $service['title'] }}</h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-navy-200">{{ $service['intro'] }}</p>

                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($wa)
                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xs bg-[#25D366] px-6 py-3.5 font-display text-small font-semibold text-white transition hover:brightness-95">
                            <x-icons.whatsapp class="h-4 w-4" /> Talk to us on WhatsApp
                        </a>
                    @endif
                    <a wire:navigate href="{{ route('contact') }}" class="group inline-flex items-center gap-2 rounded-xs {{ $wa ? 'border border-white/25 text-white hover:border-lime-400 hover:text-lime-400' : 'bg-lime-500 text-navy-900 hover:bg-lime-400' }} px-6 py-3.5 font-display text-small font-semibold transition-colors">
                        Contact us <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </a>
                </div>
            </div>

            <div data-reveal-item>
                <x-home.mock-panel :type="$service['mock']" />
            </div>
        </div>
    </section>

    {{-- What is included --}}
    <section class="bg-white">
        <div class="wb-container py-16 md:py-24">
            <div class="max-w-2xl">
                <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">What we do</p>
                <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] md:text-[2.5rem]">What is included</h2>
            </div>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($service['includes'] as $item)
                    <div class="group rounded-sm border border-paper-300 bg-white p-7 transition-all duration-300 hover:-translate-y-1 hover:border-navy-700 hover:bg-navy-700">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-lime-100 text-lime-800 transition-colors group-hover:bg-lime-500 group-hover:text-navy-900">
                            <x-icons.wb name="check" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-5 font-display text-[1.1rem] font-bold text-navy-700 transition-colors group-hover:text-white">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-small leading-relaxed text-paper-600 transition-colors group-hover:text-navy-200">{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How we work --}}
    <section class="bg-paper-100">
        <div class="wb-container py-16 md:py-24">
            <div class="mx-auto max-w-2xl text-center">
                <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">How we work</p>
                <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] md:text-[2.5rem]">From first call to ongoing support</h2>
            </div>
            <ol class="mt-12 grid gap-5 md:grid-cols-5">
                @foreach ($process as $i => $step)
                    <li class="relative rounded-sm border border-paper-300 bg-white p-6">
                        <span class="font-display text-[2rem] font-bold leading-none text-lime-600">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-4 font-display text-[1.05rem] font-bold text-navy-700">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-small leading-relaxed text-paper-600">{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Equipment - only when the matching shop categories have products --}}
    @if ($products->isNotEmpty())
        <section class="bg-white">
            <div class="wb-container py-16 md:py-24">
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div class="max-w-2xl">
                        <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">From our shop</p>
                        <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em]">Equipment we supply</h2>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($categories as $c)
                            <a wire:navigate href="{{ route('shop.category', $c->slug) }}" class="rounded-full border border-navy-700 px-4 py-2 font-display text-sm font-semibold text-navy-700 transition-colors hover:bg-navy-700 hover:text-white">{{ $c->name }} →</a>
                        @endforeach
                    </div>
                </div>
                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($products as $product)
                        <x-shop.product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Related projects --}}
    @if ($projects->isNotEmpty())
        <section class="bg-paper-100">
            <div class="wb-container py-16 md:py-24">
                <h2 class="text-h2">Work in this area</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-work.project-card :project="$project" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Other services --}}
    <section class="bg-white">
        <div class="wb-container py-16">
            <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">Other services</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                @foreach ($others as $other)
                    <a wire:navigate href="{{ route($other['route']) }}" class="group flex items-center gap-4 rounded-sm border border-paper-300 p-5 transition-colors hover:border-navy-400">
                        <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-sm bg-navy-700 text-lime-400 transition-colors group-hover:bg-lime-500 group-hover:text-navy-900">
                            <x-icons.wb :name="$other['icon']" class="h-5 w-5" />
                        </span>
                        <span class="font-display text-[15px] font-bold text-navy-700">{{ $other['title'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

</x-layouts.app>
