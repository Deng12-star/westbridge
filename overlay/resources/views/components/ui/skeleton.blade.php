@props(['class' => 'h-4 w-full'])

<div {{ $attributes->merge(['class' => 'animate-pulse rounded-xs bg-paper-200 '.$class]) }} aria-hidden="true"></div>
