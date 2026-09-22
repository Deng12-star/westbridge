@props(['items' => []])

@if (filled($items))
    <nav aria-label="Breadcrumb" {{ $attributes }}>
        <ol class="flex flex-wrap items-center gap-1.5 text-xs text-paper-600">
            <li><a wire:navigate href="{{ route('home') }}" class="transition-colors hover:text-navy-700">Home</a></li>
            @foreach ($items as $item)
                <li aria-hidden="true" class="text-paper-400">/</li>
                <li>
                    @if (! $loop->last && isset($item['url']))
                        <a wire:navigate href="{{ $item['url'] }}" class="transition-colors hover:text-navy-700">{{ $item['title'] }}</a>
                    @else
                        <span class="text-navy-700" aria-current="page">{{ $item['title'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
