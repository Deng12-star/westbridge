@props(['project'])

@php
    /*
     | Accepts a PortfolioRepository array now, an Eloquent model in Phase 3.
     | Without a screenshot, a drawn panel of the system stands in.
     */
    $title = data_get($project, 'title');
    $slug = data_get($project, 'slug');
    $client = data_get($project, 'client');
    $industry = data_get($project, 'industry');
    $summary = data_get($project, 'summary') ?? data_get($project, 'outcome');
    $image = data_get($project, 'cover_image_url') ?? data_get($project, 'image');
    $category = data_get($project, 'category');
    $status = data_get($project, 'status');
    $mock = data_get($project, 'mock', 'dashboard');
    $domain = data_get($project, 'domain');
@endphp

<a wire:navigate
    href="{{ $slug ? route('portfolio.show', $slug) : '#' }}"
    data-reveal-item
    {{ $attributes->merge(['class' => 'group flex flex-col overflow-hidden rounded-sm border border-paper-300 bg-white transition-all duration-300 ease-[var(--ease-brand)] hover:-translate-y-1.5 hover:border-navy-300 hover:shadow-[0_24px_48px_-24px_rgba(28,43,79,0.45)]']) }}
>
    <div class="relative aspect-16/10 w-full max-w-full overflow-hidden bg-navy-800">
        @if ($image)
            <img
                src="{{ $image }}"
                alt="{{ $title }}"
                loading="lazy"
                decoding="async"
                class="h-full w-full object-cover object-top transition-transform duration-500 group-hover:scale-[1.04]"
            >
        @else
            <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-lime-500/15 blur-3xl" aria-hidden="true"></div>
            <div class="absolute inset-x-6 top-8 transition-transform duration-500 ease-[var(--ease-brand)] group-hover:-translate-y-2" aria-hidden="true">
                <x-home.mock-panel :type="$mock" :label="$domain" :compact="true" />
            </div>
        @endif

        @if ($category)
            <span class="absolute left-3 top-3 rounded-xs bg-white/95 px-2 py-1 font-display text-[11px] font-semibold uppercase tracking-[0.08em] text-navy-700">
                {{ $category }}
            </span>
        @endif

        @if ($status)
            <span class="absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-xs bg-navy-900/80 px-2 py-1 font-mono text-[10px] uppercase tracking-wider text-white">
                <span @class(['h-1.5 w-1.5 rounded-full', 'bg-lime-400' => $status === 'Live', 'bg-[#E0A44B]' => $status !== 'Live'])></span>
                {{ $status }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-2 border-t border-paper-200 p-6">
        @if ($client || $industry)
            <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">
                {{ collect([$client, $industry])->filter()->join(' / ') }}
            </p>
        @endif

        <h3 class="font-display text-h3 font-bold leading-snug text-navy-700">{{ $title }}</h3>

        @if ($summary)
            <p class="text-small leading-relaxed text-paper-600">{{ $summary }}</p>
        @endif

        <span class="mt-auto inline-flex items-center gap-2 pt-4 font-display text-small font-semibold text-navy-700 transition-colors group-hover:text-lime-700">
            View project
            <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
        </span>
    </div>
</a>
