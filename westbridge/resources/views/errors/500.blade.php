<x-layouts.app title="Something went wrong">
    <section class="bg-paper-100">
        <div class="wb-container flex min-h-[60vh] flex-col items-center justify-center py-20 text-center">
            <p class="font-mono text-small text-lime-700">500</p>
            <h1 class="mt-3 text-h1">Something went wrong on our side.</h1>
            <p class="mt-4 max-w-md text-paper-600">
                The problem has been logged and our team has been notified. Nothing you entered was lost.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a wire:navigate href="{{ route('home') }}"><x-ui.button>Back to home</x-ui.button></a>
                @if (setting('contact.whatsapp'))
                    <a href="{{ whatsapp_url('Hello WestBridge, I hit an error on your website.') }}" target="_blank" rel="noopener">
                        <x-ui.button variant="outline">Tell us on WhatsApp</x-ui.button>
                    </a>
                @endif
            </div>
        </div>
    </section>
</x-layouts.app>
