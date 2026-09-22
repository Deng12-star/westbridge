@props(['title', 'message' => null, 'action' => null, 'href' => null])

{{-- One illustration, one line, one action. Used wherever content can be absent. --}}
<div class="flex flex-col items-center justify-center rounded-sm border border-dashed border-paper-300 bg-paper-50 px-6 py-14 text-center">
    <svg class="h-10 w-10 text-paper-400" viewBox="0 0 40 40" fill="none" aria-hidden="true">
        <path d="M6 28h28M10 28V16l10-7 10 7v12" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
        <circle cx="20" cy="21" r="2.5" stroke="currentColor" stroke-width="1.5"/>
    </svg>

    <h3 class="mt-4 text-h3">{{ $title }}</h3>

    @if ($message)
        <p class="mt-2 max-w-sm text-small text-paper-600">{{ $message }}</p>
    @endif

    @if ($action && $href)
        <a wire:navigate href="{{ $href }}" class="mt-6">
            <x-ui.button size="sm">{{ $action }}</x-ui.button>
        </a>
    @endif
</div>
