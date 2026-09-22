@props(['post'])

<a wire:navigate href="{{ route('news.show', $post->slug) }}" data-reveal-item
    {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-sm border border-paper-300 bg-white transition-all duration-300 ease-[var(--ease-brand)] hover:-translate-y-1 hover:border-navy-300 hover:shadow-[0_20px_40px_-24px_rgba(28,43,79,0.45)]']) }}>
    <div class="relative aspect-16/9 w-full overflow-hidden bg-navy-800">
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]">
        @else
            <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_80%_20%,rgba(137,199,38,0.22),transparent_55%)]">
                <x-icons.wb name="news" class="h-12 w-12 text-navy-400" />
            </div>
        @endif
        <span class="absolute left-3 top-3 rounded-xs bg-white/95 px-2 py-1 font-display text-[11px] font-semibold uppercase tracking-[0.08em] text-navy-700">{{ $post->category }}</span>
    </div>
    <div class="flex flex-1 flex-col gap-2 p-6">
        <p class="font-mono text-xs text-paper-500">
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j F Y') }}</time> · {{ $post->readingMinutes() }} min read
        </p>
        <h3 class="font-display text-h3 font-bold leading-snug text-navy-700 transition-colors group-hover:text-lime-700">{{ $post->title }}</h3>
        <p class="line-clamp-3 text-small leading-relaxed text-paper-600">{{ $post->summary() }}</p>
        <span class="mt-auto inline-flex items-center gap-2 pt-4 font-display text-small font-semibold text-navy-700">
            Read more <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
        </span>
    </div>
</a>
