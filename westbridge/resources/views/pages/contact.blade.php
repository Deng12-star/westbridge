<x-layouts.app :title="$page->meta_title ?? 'Contact'" :description="$page->meta_description">

    <x-page.hero
        eyebrow="Contact"
        :title="$page->heading ?? 'Talk to us.'"
        :lede="$page->lede ?? 'Tell us what you need and we will come back within one business day. For anything urgent, WhatsApp reaches us fastest.'"
    />

    <section class="bg-white">
        <div class="wb-container py-16 md:py-24">
            <div class="grid gap-12 lg:grid-cols-[1fr_1.15fr] lg:gap-16">

                {{-- Details. Every row hides itself until its setting is filled. --}}
                <div>
                    <h2 class="text-h3">{{ setting('company.name', 'WestBridge Technologies') }}</h2>

                    <dl class="mt-6 grid gap-5">
                        @if (setting('contact.address') || setting('contact.city'))
                            <div class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-lime-700" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M10 18s6-5.2 6-9.5A6 6 0 004 8.5C4 12.8 10 18 10 18z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                    <circle cx="10" cy="8.5" r="2.2" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                <div>
                                    <dt class="font-display text-small font-semibold text-navy-700">Address</dt>
                                    <dd class="mt-0.5 text-small text-paper-600">
                                        {{ setting('contact.address') }}<br>
                                        {{ collect([setting('contact.city'), setting('contact.country')])->filter()->join(', ') }}
                                    </dd>
                                </div>
                            </div>
                        @endif

                        @if (setting('contact.phone'))
                            <div class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-lime-700" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M4 4.5h3.5l1.2 3.5-1.8 1.2a10 10 0 005 5l1.2-1.8 3.5 1.2V17a1.2 1.2 0 01-1.3 1.2A14 14 0 012.8 5.8 1.2 1.2 0 014 4.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                </svg>
                                <div>
                                    <dt class="font-display text-small font-semibold text-navy-700">Phone</dt>
                                    <dd class="mt-0.5">
                                        <a href="tel:{{ setting('contact.phone') }}" class="font-mono text-small text-paper-600 hover:text-navy-700">{{ setting('contact.phone') }}</a>
                                        @if (setting('contact.phone_alt'))
                                            <br><a href="tel:{{ setting('contact.phone_alt') }}" class="font-mono text-small text-paper-600 hover:text-navy-700">{{ setting('contact.phone_alt') }}</a>
                                        @endif
                                    </dd>
                                </div>
                            </div>
                        @endif

                        @if (setting('contact.email'))
                            <div class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-lime-700" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <rect x="2.5" y="4.5" width="15" height="11" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
                                    <path d="M3 6l7 4.5L17 6" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                </svg>
                                <div>
                                    <dt class="font-display text-small font-semibold text-navy-700">Email</dt>
                                    <dd class="mt-0.5">
                                        <a href="mailto:{{ setting('contact.email') }}" class="text-small text-paper-600 hover:text-navy-700">{{ setting('contact.email') }}</a>
                                        @if (setting('contact.email_sales'))
                                            <br><a href="mailto:{{ setting('contact.email_sales') }}" class="text-small text-paper-600 hover:text-navy-700">{{ setting('contact.email_sales') }}</a>
                                        @endif
                                    </dd>
                                </div>
                            </div>
                        @endif

                        @if (setting('contact.hours'))
                            <div class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-lime-700" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <circle cx="10" cy="10" r="7.2" stroke="currentColor" stroke-width="1.4"/>
                                    <path d="M10 6v4.3l2.8 1.7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                </svg>
                                <div>
                                    <dt class="font-display text-small font-semibold text-navy-700">Business hours</dt>
                                    <dd class="mt-0.5 whitespace-pre-line text-small text-paper-600">{{ setting('contact.hours') }}</dd>
                                </div>
                            </div>
                        @endif
                    </dl>

                    @if (setting('contact.whatsapp'))
                        <a href="{{ whatsapp_url() }}" target="_blank" rel="noopener" class="mt-8 inline-flex">
                            <x-ui.button variant="outline">
                                <x-icons.whatsapp class="h-4 w-4" />
                                Message us on WhatsApp
                            </x-ui.button>
                        </a>
                    @endif

                    @php($socials = active_socials())
                    @if (filled($socials))
                        <div class="mt-8 border-t border-paper-200 pt-6">
                            <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-paper-500">Follow</p>
                            <div class="mt-3 flex gap-2">
                                @foreach ($socials as $key => $url)
                                    <a href="{{ $url }}" target="_blank" rel="noopener"
                                       class="inline-flex h-10 w-10 items-center justify-center rounded-xs border border-paper-300 text-navy-700 transition-colors hover:border-lime-500 hover:text-lime-700"
                                       aria-label="{{ ucfirst($key) }}">
                                        <x-dynamic-component :component="'icons.'.$key" class="h-4 w-4" />
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Form --}}
                <div>
                    <livewire:contact-form />
                </div>
            </div>
        </div>
    </section>

    {{-- Map renders only once a valid Google Maps embed URL is configured in admin. --}}
    @if ($mapEmbed = safe_map_embed())
        <section class="bg-paper-100">
            <div class="wb-container pb-16 md:pb-24">
                <div class="overflow-hidden rounded-sm border border-paper-300">
                    <iframe
                        src="{{ $mapEmbed }}"
                        title="Map showing the WestBridge Technologies office"
                        class="h-[380px] w-full"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>
