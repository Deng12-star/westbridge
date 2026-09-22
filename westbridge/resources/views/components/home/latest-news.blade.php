@props(['posts' => []])

{{-- Shows once three posts are live - one lonely post reads as an abandoned blog. --}}
@if (count($posts) >= 3)
    <section class="bg-white">
        <div class="wb-container py-20 md:py-28">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">News</p>
                    <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] md:text-[2.5rem]">Latest updates</h2>
                </div>
                <a wire:navigate href="{{ route('news.index') }}" class="group inline-flex flex-shrink-0 items-center gap-2 rounded-xs border border-navy-700 px-5 py-3 font-display text-small font-semibold text-navy-700 transition-colors hover:bg-navy-700 hover:text-white">
                    All news <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                </a>
            </div>
            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-news.card :post="$post" />
                @endforeach
            </div>
        </div>
    </section>
@endif
