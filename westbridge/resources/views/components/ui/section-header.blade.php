@props(['eyebrow' => null, 'title', 'lede' => null, 'align' => 'left'])

<div @class([
    'grid gap-3',
    'max-w-3xl' => $align === 'left',
    'mx-auto max-w-3xl text-center' => $align === 'center',
])>
    @if ($eyebrow)
        <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">{{ $eyebrow }}</p>
    @endif

    <h2 class="text-h2">{{ $title }}</h2>

    @if ($lede)
        <p class="text-paper-600 wb-prose @if($align === 'center') mx-auto @endif">{{ $lede }}</p>
    @endif
</div>
