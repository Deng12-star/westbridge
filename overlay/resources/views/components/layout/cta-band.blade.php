{{-- Above the footer on every page. --}}
<section class="relative overflow-hidden bg-lime-500">
    <div class="pointer-events-none absolute inset-0 opacity-[0.12]" style="background-image:linear-gradient(135deg,#1C2B4F 25%,transparent 25%),linear-gradient(225deg,#1C2B4F 25%,transparent 25%);background-size:28px 28px" aria-hidden="true"></div>

    <div class="wb-container relative py-14 md:py-16">
        <div class="flex flex-col items-start gap-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <h2 class="text-[1.75rem] font-bold leading-tight tracking-[-0.02em] text-navy-900 md:text-[2.25rem]">Have a project in mind?</h2>
                <p class="mt-2 text-navy-800">
                    Software, connectivity, infrastructure or equipment - tell us what you need and we will come back within one business day.
                </p>
            </div>
            <div class="flex flex-shrink-0 flex-col gap-3 sm:flex-row">
                <a wire:navigate href="{{ route('contact') }}" class="group">
                    <x-ui.button size="lg" variant="secondary" class="w-full justify-center sm:w-auto">
                        Contact Us
                        <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                    </x-ui.button>
                </a>
                @if (setting('contact.whatsapp'))
                    <a href="{{ whatsapp_url() }}" target="_blank" rel="noopener">
                        <x-ui.button size="lg" variant="outline" class="w-full justify-center sm:w-auto">Talk on WhatsApp</x-ui.button>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
