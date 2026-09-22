@props(['name', 'rows' => 5])

<textarea
    name="{{ $name }}"
    id="{{ $name }}"
    rows="{{ $rows }}"
    @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
    {{ $attributes->class([
        'w-full rounded-xs border bg-white px-3.5 py-3 text-body text-paper-900 transition-colors',
        'placeholder:text-paper-500',
        'focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-lime-500/30',
        'border-status-crit' => $errors->has($name),
        'border-paper-300' => ! $errors->has($name),
    ]) }}
>{{ $slot }}</textarea>
