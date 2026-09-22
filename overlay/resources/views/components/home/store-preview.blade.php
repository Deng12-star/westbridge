@props(['products' => []])

{{-- Block 07. Categories render from configuration now and from the database
     in Phase 4. The featured-product row is hidden entirely until products
     exist — an empty product strip looks worse than no strip. --}}
<section class="bg-paper-100">
    <div class="wb-container py-16 md:py-24">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <x-ui.section-header
                eyebrow="Technology store"
                title="Equipment, supplied and supported."
                lede="Laptops, phones, accessories and networking hardware — bought from the same people who will set them up and support them."
            />
            <a wire:navigate href="{{ route('shop.index') }}" class="flex-shrink-0">
                <x-ui.button variant="outline">Visit Shop</x-ui.button>
            </a>
        </div>

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (config('westbridge.navigation.shop_categories') as $category)
                <a wire:navigate href="{{ $category['url'] }}" class="wb-lift group flex items-center justify-between rounded-sm border border-paper-300 bg-white px-5 py-4 hover:border-navy-300">
                    <span class="font-display text-[15px] font-semibold text-navy-700">{{ $category['title'] }}</span>
                    <svg class="h-4 w-4 text-paper-500 transition-transform duration-150 group-hover:translate-x-0.5 group-hover:text-navy-700" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M3 8h9M8.5 4.5L12 8l-3.5 3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            @endforeach
        </div>

        @if (filled($products))
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-shop.product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>
</section>
