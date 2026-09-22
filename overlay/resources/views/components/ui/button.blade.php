@props([
    'variant' => 'primary',
    'size' => 'md',
    'as' => 'button',
    'type' => 'button',
])

@php
    $base = 'wb-press inline-flex items-center gap-2 font-display font-semibold rounded-xs disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        // Lime is the ONLY primary. It is the loudest thing on any page.
        'primary' => 'bg-lime-500 text-navy-800 hover:bg-lime-600 hover:text-white',
        'secondary' => 'bg-navy-700 text-white hover:bg-navy-600',
        'outline' => 'border-[1.5px] border-navy-700 text-navy-700 hover:bg-navy-700 hover:text-white',
        'ghost' => 'text-navy-700 hover:bg-paper-100',
        // For use on navy grounds
        'ghost-light' => 'border-[1.5px] border-navy-300 text-white hover:bg-white hover:text-navy-700',
    ];

    $sizes = [
        'sm' => 'h-10 px-4 text-[14px]',
        'md' => 'h-12 px-6 text-[15px]',
        'lg' => 'h-14 px-8 text-[16px]',
    ];

    $classes = implode(' ', [$base, $variants[$variant] ?? $variants['primary'], $sizes[$size] ?? $sizes['md']]);
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
