@props(['product'])

@php
    /*
     | Accepts a Product model or a plain array with the same keys.
     | NOTE: this component must exist even while the shop is empty - Blade
     | resolves <x-...> tags at compile time.
     */
    $name = data_get($product, 'name');
    $slug = data_get($product, 'slug');
    $brand = data_get($product, 'brand');
    $summary = data_get($product, 'short_description');
    $price = data_get($product, 'price');
    $currency = data_get($product, 'currency', 'USD');
    $image = data_get($product, 'image_url') ?? data_get($product, 'image');
    $stock = data_get($product, 'stock_status', 'in_stock');
    $category = data_get($product, 'category.name');
    $showPrices = (bool) (int) setting('shop.show_prices', '1');

    [$stockLabel, $stockClasses] = match ($stock) {
        'on_order' => ['Available to order', 'bg-navy-50 text-navy-700'],
        'out_of_stock' => ['Out of stock', 'bg-status-crit/10 text-status-crit'],
        default => ['In stock', 'bg-lime-100 text-lime-800'],
    };
@endphp

<a wire:navigate
    href="{{ $slug ? route('shop.product', $slug) : '#' }}"
    data-reveal-item
    {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-sm border border-paper-300 bg-white transition-all duration-300 ease-[var(--ease-brand)] hover:-translate-y-1 hover:border-navy-300 hover:shadow-[0_20px_40px_-24px_rgba(28,43,79,0.45)]']) }}
>
    <div class="relative aspect-square w-full max-w-full overflow-hidden bg-paper-50">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy" decoding="async" width="600" height="600"
                class="h-full w-full object-contain p-5 transition-transform duration-500 group-hover:scale-[1.05]">
        @else
            <div class="flex h-full w-full items-center justify-center text-paper-300">
                <x-icons.wb name="device" class="h-14 w-14" />
            </div>
        @endif

        <span class="absolute left-3 top-3 rounded-xs px-2 py-1 font-display text-[11px] font-semibold uppercase tracking-[0.06em] {{ $stockClasses }}">{{ $stockLabel }}</span>
    </div>

    <div class="flex flex-1 flex-col gap-1 border-t border-paper-200 p-5">
        @if ($category || $brand)
            <p class="font-display text-[11px] font-semibold uppercase tracking-[0.14em] text-lime-700">{{ collect([$brand, $category])->filter()->join(' · ') }}</p>
        @endif
        <h3 class="font-display text-[1.05rem] font-bold leading-snug text-navy-700">{{ $name }}</h3>
        @if ($summary)
            <p class="line-clamp-2 text-small text-paper-600">{{ $summary }}</p>
        @endif

        <div class="mt-auto flex items-end justify-between gap-3 pt-4">
            @if ($showPrices && is_numeric($price) && (float) $price > 0)
                <span class="font-display text-lg font-bold text-navy-700">{{ $currency }} {{ number_format((float) $price, 2) }}</span>
            @else
                <span class="font-display text-small font-semibold text-lime-700">Price on request</span>
            @endif
            <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-paper-100 text-navy-700 transition-colors group-hover:bg-lime-500 group-hover:text-navy-900" aria-hidden="true">
                <x-icons.wb name="arrow" class="h-4 w-4" />
            </span>
        </div>
    </div>
</a>
