@props(['products' => []])

{{-- Shows once at least four products are live; an empty strip would do more harm than none. --}}
@if (count($products) >= 4)
    <section class="bg-paper-100">
        <div class="wb-container py-20 md:py-28">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">Shop</p>
                    <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] md:text-[2.5rem]">Technology products</h2>
                    <p class="mt-4 text-paper-600">Equipment we supply, set up and support. Order any item on WhatsApp.</p>
                </div>
                <a wire:navigate href="{{ route('shop.index') }}" class="group inline-flex flex-shrink-0 items-center gap-2 rounded-xs border border-navy-700 px-5 py-3 font-display text-small font-semibold text-navy-700 transition-colors hover:bg-navy-700 hover:text-white">
                    Visit the shop
                    <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                </a>
            </div>
            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-shop.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif
