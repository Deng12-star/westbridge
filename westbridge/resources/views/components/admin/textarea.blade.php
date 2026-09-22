@props(['name', 'label', 'value' => null, 'rows' => 4, 'help' => null, 'required' => false])

@php($id = 'f-'.\Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', $name)))
@php($key = str_replace(['[', ']'], ['.', ''], $name))

<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="block font-display text-sm font-semibold text-navy-700">
        {{ $label }} @if ($required)<span class="text-status-crit">*</span>@endif
    </label>
    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->except('class')->merge(['class' => 'mt-1.5 block w-full rounded-xs border border-paper-300 bg-white px-3 py-2.5 text-[15px] leading-relaxed text-paper-900 placeholder:text-paper-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20']) }}
    >{{ old($key, $value) }}</textarea>
    @if ($help)<p class="mt-1 text-xs text-paper-500">{{ $help }}</p>@endif
    @error($key)<p class="mt-1 text-xs text-status-crit">{{ $message }}</p>@enderror
</div>
