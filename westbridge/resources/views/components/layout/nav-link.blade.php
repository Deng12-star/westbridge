@props(['href', 'active' => false])

<a wire:navigate
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->class([
        'wb-navlink px-3 py-2 font-display text-[15px] font-medium transition-colors',
    ]) }}
>{{ $slot }}</a>
