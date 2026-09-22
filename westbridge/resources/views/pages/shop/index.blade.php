@php
    $title = $category?->name ?? 'Shop';
    $lede = $category?->description ?: setting('shop.intro', 'Laptops, phones, networking and Starlink equipment. See something you need? Message us on WhatsApp and we will confirm price and availability.');
@endphp

<x-layouts.app :title="$title" :description="\Illuminate\Support\Str::limit(strip_tags($lede), 155)">

    <section class="relative overflow-hidden bg-navy-900 text-white">
        <div class="pointer-events-none absolute -right-40 -top-40 h-[480px] w-[480px] rounded-full bg-lime-500/10 blur-3xl" aria-hidden="true"></div>
        <div class="wb-container relative py-14 md:py-20">
            <nav aria-label="Breadcrumb" class="text-xs text-navy-300">
                <a wire:navigate href="{{ route('home') }}" class="hover:text-lime-400">Home</a>
                <span class="mx-1.5 text-navy-500">/</span>
                @if ($category)
                    <a wire:navigate href="{{ route('shop.index') }}" class="hover:text-lime-400">Shop</a>
                    <span class="mx-1.5 text-navy-500">/</span>
                    <span class="text-white">{{ $category->name }}</span>
                @else
                    <span class="text-white">Shop</span>
                @endif
            </nav>
            <p class="mt-8 font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400">Technology Products</p>
            <h1 class="mt-3 text-[2.25rem] font-bold leading-tight tracking-[-0.02em] text-white md:text-[3rem]">{{ $category ? $category->name : 'Shop' }}</h1>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-navy-200">{{ $lede }}</p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/5 px-4 py-2 text-small text-navy-200">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#25D366] text-white"><x-icons.whatsapp class="h-3.5 w-3.5" /></span>
                    Order any item on WhatsApp
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/5 px-4 py-2 text-small text-navy-200">
                    <x-icons.wb name="check" class="h-4 w-4 text-lime-400" /> Setup and support from our team
                </span>
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="wb-container py-12 md:py-16">
            {{-- Filters --}}
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 lg:mx-0 lg:flex-wrap lg:px-0" data-no-stagger>
                    <a wire:navigate href="{{ route('shop.index', array_filter(['q' => $term])) }}" @class([
                        'flex-shrink-0 rounded-full px-4 py-2 font-display text-sm font-semibold transition-colors',
                        'bg-navy-700 text-white' => ! $category,
                        'border border-paper-300 text-navy-700 hover:border-navy-400' => $category,
                    ])>All</a>
                    @foreach ($categories as $c)
                        <a wire:navigate href="{{ route('shop.category', array_filter(['category' => $c->slug, 'q' => $term])) }}" @class([
                            'flex-shrink-0 rounded-full px-4 py-2 font-display text-sm font-semibold transition-colors',
                            'bg-navy-700 text-white' => $category?->is($c),
                            'border border-paper-300 text-navy-700 hover:border-navy-400' => ! $category?->is($c),
                        ])>{{ $c->name }} @if ($c->products_count)<span class="ml-1 font-mono text-xs opacity-60">{{ $c->products_count }}</span>@endif</a>
                    @endforeach
                </div>

                <form method="GET" action="{{ $category ? route('shop.category', $category->slug) : route('shop.index') }}" class="relative w-full lg:w-72" role="search">
                    <label for="shop-q" class="sr-only">Search products</label>
                    <input id="shop-q" type="search" name="q" value="{{ $term }}" placeholder="Search products"
                        class="w-full rounded-full border border-paper-300 bg-white py-2.5 pl-4 pr-11 text-[15px] focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                    <button class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-lime-500 text-navy-900" aria-label="Search">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none"><circle cx="9" cy="9" r="5.5" stroke="currentColor" stroke-width="1.8"/><path d="M13.5 13.5L17 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </form>
            </div>

            @if ($term !== '')
                <p class="mt-6 text-small text-paper-600">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('result', $products->total()) }} for “{{ $term }}”. <a wire:navigate href="{{ $category ? route('shop.category', $category->slug) : route('shop.index') }}" class="font-semibold text-navy-700 underline">Clear</a></p>
            @endif

            @if ($products->isEmpty())
                <div class="mt-10 rounded-sm border border-dashed border-paper-300 bg-paper-50 px-6 py-16 text-center">
                    <x-icons.wb name="device" class="mx-auto h-10 w-10 text-paper-400" />
                    <p class="mt-4 font-display text-lg font-bold text-navy-700">
                        @if ($term !== '') Nothing matches “{{ $term }}”.
                        @elseif ($totalProducts === 0) Our catalogue is being added.
                        @else Nothing listed in {{ $category?->name }} right now.
                        @endif
                    </p>
                    <p class="mx-auto mt-2 max-w-md text-small text-paper-600">Tell us what you are looking for and we will tell you what we can supply.</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        @if ($wa = whatsapp_url('Hello WestBridge, I am looking for '.($term !== '' ? $term : ($category?->name ?? 'a product')).'.'))
                            <a href="{{ $wa }}" target="_blank" rel="noopener nofollow" class="inline-flex items-center gap-2 rounded-xs bg-[#25D366] px-5 py-3 font-display text-small font-semibold text-white hover:brightness-95">
                                <x-icons.whatsapp class="h-4 w-4" /> Ask on WhatsApp
                            </a>
                        @endif
                        <a wire:navigate href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-xs border border-navy-700 px-5 py-3 font-display text-small font-semibold text-navy-700 hover:bg-navy-700 hover:text-white">Contact us</a>
                    </div>
                </div>
            @else
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <x-shop.product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-10">{{ $products->links() }}</div>
            @endif
        </div>
    </section>

</x-layouts.app>
