@props(['variant' => 'info', 'title' => null])

@php
    $styles = [
        'success' => 'border-status-good/30 bg-status-good/8 text-status-good',
        'warning' => 'border-status-warn/30 bg-status-warn/8 text-status-warn',
        'error' => 'border-status-crit/30 bg-status-crit/8 text-status-crit',
        'info' => 'border-navy-200 bg-navy-50 text-navy-700',
    ];
@endphp

<div role="alert" {{ $attributes->class(['rounded-xs border-l-[3px] px-4 py-3', $styles[$variant] ?? $styles['info']]) }}>
    @if ($title)
        <p class="font-display text-small font-semibold">{{ $title }}</p>
    @endif
    <div class="text-small @if($title) mt-1 @endif">{{ $slot }}</div>
</div>
