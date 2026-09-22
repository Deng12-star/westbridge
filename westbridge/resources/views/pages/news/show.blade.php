@php
    $url = route('news.show', $post->slug);
    $shareText = rawurlencode($post->title.' '.$url);
@endphp

<x-layouts.app :title="$post->title" :description="$post->summary(155)" :og-image="$post->coverUrl()">

    @push('head')
        <meta property="article:published_time" content="{{ $post->published_at->toAtomString() }}">
        <script type="application/ld+json">{!! json_encode(app(\App\Services\Seo\SeoService::class)->articleSchema($post), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @endpush

    <article>
        <header class="relative overflow-hidden bg-navy-900 text-white">
            <div class="pointer-events-none absolute -right-40 -top-40 h-[480px] w-[480px] rounded-full bg-lime-500/10 blur-3xl" aria-hidden="true"></div>
            <div class="wb-container relative py-14 md:py-20">
                <nav aria-label="Breadcrumb" class="text-xs text-navy-300">
                    <a wire:navigate href="{{ route('home') }}" class="hover:text-lime-400">Home</a>
                    <span class="mx-1.5 text-navy-500">/</span>
                    <a wire:navigate href="{{ route('news.index') }}" class="hover:text-lime-400">News</a>
                </nav>
                <div class="mx-auto max-w-3xl text-center">
                    <p class="mt-8 font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400">{{ $post->category }}</p>
                    <h1 class="mt-3 text-[2rem] font-bold leading-[1.15] tracking-[-0.02em] text-white md:text-[2.75rem]">{{ $post->title }}</h1>
                    <p class="mt-5 font-mono text-xs text-navy-300">
                        <time datetime="{{ $post->published_at->toAtomString() }}">{{ $post->published_at->format('j F Y') }}</time>
                        · {{ $post->readingMinutes() }} min read
                        @if ($post->author) · {{ $post->author->name }} @endif
                    </p>
                </div>
            </div>
        </header>

        <div class="bg-white">
            <div class="wb-container py-12 md:py-16">
                @if ($post->coverUrl())
                    <div class="mx-auto -mt-24 mb-12 max-w-4xl overflow-hidden rounded-sm border border-paper-200 shadow-[0_30px_60px_-30px_rgba(28,43,79,0.5)] md:-mt-28">
                        <img src="{{ $post->coverUrl() }}" alt="" class="h-auto w-full" decoding="async">
                    </div>
                @endif

                <div class="wb-article mx-auto max-w-2xl text-[1.0625rem] leading-[1.8] text-paper-800">
                    {!! $post->body_html !!}
                </div>

                <div class="mx-auto mt-12 flex max-w-2xl flex-wrap items-center gap-3 border-t border-paper-200 pt-6">
                    <span class="font-display text-sm font-semibold text-navy-700">Share</span>
                    <a href="https://wa.me/?text={{ $shareText }}" target="_blank" rel="noopener nofollow" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#25D366] text-white hover:brightness-95" aria-label="Share on WhatsApp"><x-icons.whatsapp class="h-4 w-4" /></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($url) }}" target="_blank" rel="noopener nofollow" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-paper-300 text-navy-700 hover:border-navy-500" aria-label="Share on Facebook"><x-icons.facebook class="h-4 w-4" /></a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode($url) }}" target="_blank" rel="noopener nofollow" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-paper-300 text-navy-700 hover:border-navy-500" aria-label="Share on LinkedIn"><x-icons.linkedin class="h-4 w-4" /></a>
                    <a href="https://twitter.com/intent/tweet?text={{ $shareText }}" target="_blank" rel="noopener nofollow" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-paper-300 text-navy-700 hover:border-navy-500" aria-label="Share on X"><x-icons.twitter class="h-4 w-4" /></a>
                </div>
            </div>
        </div>
    </article>

    @if ($more->isNotEmpty())
        <section class="bg-paper-100">
            <div class="wb-container py-14 md:py-20">
                <div class="flex items-end justify-between gap-6">
                    <h2 class="text-h2">More news</h2>
                    <a wire:navigate href="{{ route('news.index') }}" class="font-display text-small font-semibold text-navy-700 hover:text-lime-700">All news →</a>
                </div>
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($more as $item)
                        <x-news.card :post="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>
