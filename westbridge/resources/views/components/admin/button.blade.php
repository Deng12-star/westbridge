@props(['variant' => 'primary', 'href' => null, 'type' => 'submit'])

@php
    $classes = 'inline-flex items-center justify-center gap-2 rounded-xs px-4 py-2.5 font-display text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-navy-500/40 ';
    $classes .= match ($variant) {
        'primary' => 'bg-lime-500 text-navy-900 hover:bg-lime-400',
        'dark' => 'bg-navy-700 text-white hover:bg-navy-800',
        'outline' => 'border border-paper-300 bg-white text-navy-700 hover:border-navy-400',
        'danger' => 'border border-status-crit/30 bg-white text-status-crit hover:bg-status-crit/5',
        default => 'text-navy-700 hover:bg-paper-200',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
