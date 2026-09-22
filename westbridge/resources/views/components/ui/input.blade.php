@props(['name', 'type' => 'text'])

<input
    type="{{ $type }}"
    name="{{ $name }}"
    id="{{ $name }}"
    @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
    {{ $attributes->class([
        'h-12 w-full rounded-xs border bg-white px-3.5 text-body text-paper-900 transition-colors',
        'placeholder:text-paper-500',
        'focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-lime-500/30',
        'border-status-crit' => $errors->has($name),
        'border-paper-300' => ! $errors->has($name),
    ]) }}
>
