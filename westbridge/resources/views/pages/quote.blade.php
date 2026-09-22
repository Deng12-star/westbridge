<x-layouts.app :title="$page->meta_title ?? 'Request a Quote'" :description="$page->meta_description">

    <x-page.hero
        eyebrow="Request a quote"
        :title="$page->heading ?? 'Tell us what you need.'"
        :lede="$page->lede ?? 'Three short steps. We reply within one business day with a scoped approach and a written quotation.'"
    />

    <section class="bg-white">
        <div class="wb-container py-16 md:py-20">
            <livewire:quote-request-form />

            <div class="mx-auto mt-10 max-w-2xl rounded-sm border border-paper-200 bg-paper-50 p-5">
                <p class="text-small text-paper-600">
                    Prefer to talk first?
                    @if (setting('contact.phone'))
                        Call <a href="tel:{{ setting('contact.phone') }}" class="font-mono text-navy-700 underline underline-offset-2">{{ setting('contact.phone') }}</a>
                    @endif
                    @if (setting('contact.whatsapp'))
                        or <a href="{{ whatsapp_url() }}" target="_blank" rel="noopener" class="text-navy-700 underline underline-offset-2">message us on WhatsApp</a>
                    @endif
                    — or use the <a wire:navigate href="{{ route('contact') }}" class="text-navy-700 underline underline-offset-2">contact form</a>.
                </p>
            </div>
        </div>
    </section>

</x-layouts.app>
