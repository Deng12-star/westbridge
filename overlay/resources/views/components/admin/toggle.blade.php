@props(['name', 'label', 'checked' => false, 'help' => null])

@php($id = 'f-'.\Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', $name)))
@php($key = str_replace(['[', ']'], ['.', ''], $name))
@php($on = (bool) old($key, $checked))

{{-- The hidden 0 makes an unticked box submit "off" instead of nothing. --}}
<label for="{{ $id }}" {{ $attributes->merge(['class' => 'flex cursor-pointer items-start gap-3']) }}>
    <input type="hidden" name="{{ $name }}" value="0">
    <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="1" @checked($on) class="peer sr-only">
    <span class="relative mt-0.5 inline-flex h-6 w-11 flex-shrink-0 rounded-full bg-paper-300 transition-colors peer-checked:bg-lime-500 peer-focus-visible:ring-2 peer-focus-visible:ring-navy-500/40 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
    <span>
        <span class="block font-display text-sm font-semibold text-navy-700">{{ $label }}</span>
        @if ($help)<span class="block text-xs text-paper-500">{{ $help }}</span>@endif
    </span>
</label>
