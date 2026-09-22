@php
    $showPrices = (bool) (int) setting('shop.show_prices', '1');
    $price = $showPrices ? $product->formattedPrice() : null;
    $paragraphs = collect(preg_split('/\R\s*\R/', (string) $product->description) ?: [])->map(fn ($p) => trim((string) $p))->filter();
@endphp

<x-layouts.app :title="$product->name" :description="$product->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 155)" :og-image="$product->image_url">

    @push('head')
        <script type="application/ld+json">{!! json_encode(app(\App\Services\Seo\SeoService::class)->productSchema($product), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endpush

    <section class="border-b border-paper-200 bg-paper-100">
        <div class="wb-container py-5">
            <nav aria-label="Breadcrumb" class="text-xs text-paper-600">
                <a wire:navigate href="{{ route('home') }}" class="hover:text-navy-700">Home</a>
                <span class="mx-1.5 text-paper-400">/</span>
                <a wire:navigate href="{{ route('shop.index') }}" class="hover:text-navy-700">Shop</a>
                @if ($product->category)
                    <span class="mx-1.5 text-paper-400">/</span>
                    <a wire:navigate href="{{ route('shop.category', $product->category->slug) }}" class="hover:text-navy-700">{{ $product->category->name }}</a>
                @endif
                <span class="mx-1.5 text-paper-400">/</span>
                <span class="text-navy-700" aria-current="page">{{ $product->name }}</span>
            </nav>
        </div>
    </section>

    <section class="bg-white">
        <div class="wb-container grid gap-10 py-12 md:py-16 lg:grid-cols-2 lg:gap-16">
            <div class="lg:sticky lg:top-28 lg:self-start">
                <div class="flex aspect-square w-full items-center justify-center overflow-hidden rounded-sm border border-paper-200 bg-paper-50">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-contain p-8" decoding="async" width="800" height="800">
                    @else
                        <x-icons.wb name="device" class="h-20 w-20 text-paper-300" />
                    @endif
                </div>
            </div>

            <div>
                @if ($product->brand || $product->category)
                    <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">{{ collect([$product->brand, $product->category?->name])->filter()->join(' · ') }}</p>
                @endif
                <h1 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] text-navy-700 md:text-[2.5rem]">{{ $product->name }}</h1>
                @if ($product->sku)
                    <p class="mt-2 font-mono text-small text-paper-500">SKU {{ $product->sku }}</p>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-4">
                    <span class="font-display text-[1.75rem] font-bold text-navy-700">{{ $price ?? 'Price on request' }}</span>
                    <span @class([
                        'rounded-xs px-2.5 py-1 font-display text-xs font-semibold uppercase tracking-[0.06em]',
                        'bg-lime-100 text-lime-800' => $product->stock_status === 'in_stock',
                        'bg-navy-50 text-navy-700' => $product->stock_status === 'on_order',
                        'bg-status-crit/10 text-status-crit' => $product->stock_status === 'out_of_stock',
                    ])>{{ $product->stockLabel() }}</span>
                </div>

                @if ($product->short_description)
                    <p class="mt-6 text-lg leading-relaxed text-paper-700">{{ $product->short_description }}</p>
                @endif

                {{-- The one action on the page --}}
                <div class="mt-8 rounded-sm border border-paper-300 bg-paper-50 p-6">
                    @if ($whatsapp)
                        <a href="{{ route('shop.whatsapp', $product->slug) }}" target="_blank" rel="noopener nofollow"
                            class="group flex w-full items-center justify-center gap-3 rounded-xs bg-[#25D366] px-6 py-4 font-display text-base font-bold text-white shadow-[0_12px_24px_-12px_rgba(37,211,102,0.8)] transition-all hover:-translate-y-0.5 hover:brightness-95">
                            <x-icons.whatsapp class="h-5 w-5" />
                            {{ $product->stock_status === 'out_of_stock' ? 'Ask on WhatsApp when it is back' : 'Order on WhatsApp' }}
                        </a>
                        <p class="mt-3 text-center text-small text-paper-600">Opens WhatsApp with this product already in the message. We confirm price, availability and delivery there.</p>
                    @else
                        <a wire:navigate href="{{ route('contact', ['product' => $product->name]) }}"
                            class="flex w-full items-center justify-center gap-3 rounded-xs bg-lime-500 px-6 py-4 font-display text-base font-bold text-navy-900 transition-colors hover:bg-lime-400">
                            Ask about this product <x-icons.wb name="arrow" class="h-4 w-4" />
                        </a>
                        @if (setting('contact.phone'))
                            <p class="mt-3 text-center text-small text-paper-600">Or call <a href="tel:{{ setting('contact.phone') }}" class="font-mono font-semibold text-navy-700">{{ setting('contact.phone') }}</a></p>
                        @endif
                    @endif
                </div>

                @if ($paragraphs->isNotEmpty())
                    <div class="mt-10 grid gap-4 text-[15px] leading-relaxed text-paper-700">
                        @foreach ($paragraphs as $paragraph)
                            <p>{!! nl2br(e($paragraph)) !!}</p>
                        @endforeach
                    </div>
                @endif

                @if (filled($product->specs))
                    <h2 class="mt-10 font-display text-lg font-bold text-navy-700">Specifications</h2>
                    <dl class="mt-4 divide-y divide-paper-200 overflow-hidden rounded-sm border border-paper-200">
                        @foreach ($product->specs as $spec)
                            <div class="grid grid-cols-[minmax(0,2fr)_minmax(0,3fr)] gap-4 px-4 py-3 text-small odd:bg-paper-50">
                                <dt class="font-semibold text-navy-700">{{ $spec['label'] ?? '' }}</dt>
                                <dd class="text-paper-700">{{ $spec['value'] ?? '' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="bg-paper-100">
            <div class="wb-container py-14 md:py-20">
                <div class="flex items-end justify-between gap-6">
                    <h2 class="text-h2">You may also need</h2>
                    <a wire:navigate href="{{ $product->category ? route('shop.category', $product->category->slug) : route('shop.index') }}" class="font-display text-small font-semibold text-navy-700 hover:text-lime-700">See all →</a>
                </div>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-shop.product-card :product="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>
