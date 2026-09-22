<x-layouts.app title="Page not found">
    <section class="bg-paper-100">
        <div class="wb-container flex min-h-[60vh] flex-col items-center justify-center py-20 text-center">
            <p class="font-mono text-small text-lime-700">404</p>
            <h1 class="mt-3 text-h1">We could not find that page.</h1>
            <p class="mt-4 max-w-md text-paper-600">
                The link may be out of date, or the page may have moved. Try the store or our services from here.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a wire:navigate href="{{ route('home') }}"><x-ui.button>Back to home</x-ui.button></a>
                <a wire:navigate href="{{ route('shop.index') }}"><x-ui.button variant="outline">Visit the shop</x-ui.button></a>
                <a wire:navigate href="{{ route('services.index') }}"><x-ui.button variant="ghost">Our services</x-ui.button></a>
            </div>
        </div>
    </section>
</x-layouts.app>
