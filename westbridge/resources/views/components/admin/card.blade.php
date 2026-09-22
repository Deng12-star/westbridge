@props(['title' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'rounded-sm border border-paper-300 bg-white']) }}>
    @if ($title)
        <div class="border-b border-paper-200 px-6 py-4">
            <h2 class="font-display text-[1.05rem] font-bold text-navy-700">{{ $title }}</h2>
            @if ($description)
                <p class="mt-0.5 text-small text-paper-600">{{ $description }}</p>
            @endif
        </div>
    @endif
    <div class="p-6">{{ $slot }}</div>
</div>
