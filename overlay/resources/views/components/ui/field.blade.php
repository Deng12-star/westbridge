@props(['label', 'name', 'required' => false, 'help' => null])

{{-- Real labels, never placeholder-as-label. Errors are tied via aria-describedby. --}}
<div class="grid gap-1.5">
    <label for="{{ $name }}" class="font-display text-small font-medium text-navy-700">
        {{ $label }}
        @if ($required)
            <span class="text-status-crit" aria-hidden="true">*</span>
            <span class="sr-only">(required)</span>
        @endif
    </label>

    {{ $slot }}

    @if ($help)
        <p id="{{ $name }}-help" class="text-xs text-paper-600">{{ $help }}</p>
    @endif

    @error($name)
        <p id="{{ $name }}-error" class="flex items-start gap-1.5 text-xs text-status-crit">
            <svg class="mt-0.5 h-3.5 w-3.5 flex-shrink-0" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.2"/>
                <path d="M7 4v3.5M7 9.8v.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
