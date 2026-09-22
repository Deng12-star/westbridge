{{-- Phase 1 scaffold. Replaced by the real page in the phase shown. --}}
<x-layouts.app :title="$title">
    <section class="bg-paper-100">
        <div class="wb-container py-16 md:py-24">
            <x-ui.breadcrumbs :items="[['title' => $title]]" class="mb-6" />
            <h1 class="text-h1">{{ $title }}</h1>
            <p class="mt-4 max-w-xl text-paper-600">
                This page is scheduled for Phase {{ $phase }} of the approved delivery plan.
                The route, navigation and layout are already in place.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a wire:navigate href="{{ route('home') }}"><x-ui.button variant="outline" size="sm">Back to home</x-ui.button></a>
                <a wire:navigate href="{{ route('contact') }}"><x-ui.button size="sm">Contact Us</x-ui.button></a>
            </div>
        </div>
    </section>
</x-layouts.app>
