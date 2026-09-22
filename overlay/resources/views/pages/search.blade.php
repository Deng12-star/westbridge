<x-layouts.app title="Search" description="Search WestBridge Technologies products and projects.">
    <x-page.hero eyebrow="Search" :title="$term !== '' ? 'Results for “'.$term.'”' : 'Search'" />

    <section class="bg-white">
        <div class="wb-container py-12 md:py-16">
            <form method="GET" action="{{ route('search') }}" class="flex max-w-xl gap-3" role="search">
                <label for="site-q" class="sr-only">Search</label>
                <input id="site-q" type="search" name="q" value="{{ $term }}" placeholder="Products, projects…" autofocus
                    class="w-full rounded-xs border border-paper-300 px-4 py-3 text-[15px] focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
                <button class="rounded-xs bg-lime-500 px-5 font-display text-small font-semibold text-navy-900 hover:bg-lime-400">Search</button>
            </form>

            @if (mb_strlen($term) >= 2)
                @if ($products->isEmpty() && $projects->isEmpty())
                    <p class="mt-10 text-paper-600">Nothing found. Try a shorter word, or <a wire:navigate href="{{ route('contact') }}" class="font-semibold text-navy-700 underline">ask us directly</a>.</p>
                @endif

                @if ($products->isNotEmpty())
                    <h2 class="mt-12 text-h2">Products</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($products as $product)
                            <x-shop.product-card :product="$product" />
                        @endforeach
                    </div>
                @endif

                @if ($projects->isNotEmpty())
                    <h2 class="mt-12 text-h2">Projects</h2>
                    <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($projects as $project)
                            <x-work.project-card :project="$project" />
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </section>
</x-layouts.app>
