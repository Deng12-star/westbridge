@props(['eyebrow' => null, 'title', 'lede' => null])

{{-- Interior page hero. Deliberately quieter than the homepage hero: shorter,
     no illustration, and the breadcrumb carries orientation instead. --}}
<section class="border-b border-paper-200 bg-paper-100">
    <div class="wb-container py-12 md:py-16">
        <x-ui.breadcrumbs :items="[['title' => $title]]" class="mb-5" />

        @if ($eyebrow)
            <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">{{ $eyebrow }}</p>
        @endif

        <h1 class="mt-3 text-h1 max-w-3xl">{{ $title }}</h1>

        @if ($lede)
            <p class="mt-4 text-lg text-paper-600 wb-prose">{{ $lede }}</p>
        @endif
    </div>
</section>
