<x-layouts.app title="News & updates" description="News, updates and announcements from WestBridge Technologies in Juba, South Sudan.">

    <x-page.hero eyebrow="News" title="News & updates" lede="Announcements, completed work and what is new at WestBridge Technologies." />

    <section class="bg-white">
        <div class="wb-container py-12 md:py-16">
            @if ($categories->count() > 1)
                <div class="mb-8 flex flex-wrap gap-2" data-no-stagger>
                    <a wire:navigate href="{{ route('news.index') }}" @class([
                        'rounded-full px-4 py-2 font-display text-sm font-semibold transition-colors',
                        'bg-navy-700 text-white' => ! $category,
                        'border border-paper-300 text-navy-700 hover:border-navy-400' => $category,
                    ])>All</a>
                    @foreach ($categories as $c)
                        <a wire:navigate href="{{ route('news.index', ['category' => $c]) }}" @class([
                            'rounded-full px-4 py-2 font-display text-sm font-semibold transition-colors',
                            'bg-navy-700 text-white' => $category === $c,
                            'border border-paper-300 text-navy-700 hover:border-navy-400' => $category !== $c,
                        ])>{{ $c }}</a>
                    @endforeach
                </div>
            @endif

            @if ($posts->isEmpty())
                <div class="rounded-sm border border-dashed border-paper-300 bg-paper-50 px-6 py-16 text-center">
                    <x-icons.wb name="news" class="mx-auto h-10 w-10 text-paper-400" />
                    <p class="mt-4 font-display text-lg font-bold text-navy-700">No news yet.</p>
                    <p class="mt-1 text-small text-paper-600">Check back soon, or follow us for updates.</p>
                </div>
            @else
                @php($lead = $posts->onFirstPage() && ! $category ? $posts->first() : null)

                @if ($lead)
                    {{-- The newest post, featured --}}
                    <a wire:navigate href="{{ route('news.show', $lead->slug) }}" data-reveal-item class="group mb-10 grid overflow-hidden rounded-sm border border-paper-300 bg-white transition-colors hover:border-navy-300 lg:grid-cols-2">
                        <div class="relative aspect-16/9 overflow-hidden bg-navy-800 lg:aspect-auto">
                            @if ($lead->coverUrl())
                                <img src="{{ $lead->coverUrl() }}" alt="" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
                            @else
                                <div class="flex h-full min-h-[240px] w-full items-center justify-center bg-[radial-gradient(circle_at_80%_20%,rgba(137,199,38,0.22),transparent_55%)]"><x-icons.wb name="news" class="h-14 w-14 text-navy-400" /></div>
                            @endif
                        </div>
                        <div class="flex flex-col justify-center p-8 md:p-10">
                            <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">Latest · {{ $lead->category }}</p>
                            <h2 class="mt-3 text-[1.75rem] font-bold leading-tight text-navy-700 group-hover:text-lime-700 md:text-[2.25rem]">{{ $lead->title }}</h2>
                            <p class="mt-4 leading-relaxed text-paper-600">{{ $lead->summary(260) }}</p>
                            <p class="mt-6 font-mono text-xs text-paper-500">{{ $lead->published_at->format('j F Y') }} · {{ $lead->readingMinutes() }} min read</p>
                        </div>
                    </a>
                @endif

                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        @continue($lead && $post->is($lead))
                        <x-news.card :post="$post" />
                    @endforeach
                </div>

                <div class="mt-10">{{ $posts->links() }}</div>
            @endif
        </div>
    </section>

</x-layouts.app>
