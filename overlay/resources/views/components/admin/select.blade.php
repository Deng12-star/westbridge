@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'help' => null])

@php($id = 'f-'.\Illuminate\Support\Str::slug($name))
@php($current = (string) old($name, $value))

<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="block font-display text-sm font-semibold text-navy-700">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}" class="mt-1.5 block w-full rounded-xs border border-paper-300 bg-white px-3 py-2.5 text-[15px] text-paper-900 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($current === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($help)<p class="mt-1 text-xs text-paper-500">{{ $help }}</p>@endif
    @error($name)<p class="mt-1 text-xs text-status-crit">{{ $message }}</p>@enderror
</div>
