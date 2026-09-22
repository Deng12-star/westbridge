{{--
    Footer on the reference-site pattern: brand and socials, quick links,
    services, and a labelled contact column. Every contact row and social icon
    renders only once its value is configured - nothing here is invented.
--}}
<footer class="relative overflow-hidden bg-navy-900 text-navy-200">
    <div class="pointer-events-none absolute -left-32 bottom-0 h-80 w-80 rounded-full bg-lime-500/[0.06] blur-3xl" aria-hidden="true"></div>

    <div class="wb-container relative py-16 md:py-20">
        <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1.4fr]">

            {{-- Brand --}}
            <div>
                <x-brand.logo variant="reverse" class="h-16" />
                <p class="mt-6 max-w-xs text-small leading-relaxed text-navy-300">
                    {{ setting('company.short_description', 'Technology company building software, delivering IT infrastructure and connectivity, and supplying technology products in South Sudan.') }}
                </p>

                @php $socials = active_socials(); @endphp
                @if (filled($socials))
                    <div class="mt-7 flex gap-2.5">
                        @foreach ($socials as $key => $url)
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-navy-600 text-navy-200 transition-all duration-200 hover:-translate-y-0.5 hover:border-lime-500 hover:bg-lime-500 hover:text-navy-900"
                                aria-label="{{ ucfirst($key) }}"
                            >
                                <x-dynamic-component :component="'icons.'.$key" class="h-4 w-4" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Quick links --}}
            <div>
                <h3 class="font-display text-[1.05rem] font-bold text-white">Quick Links</h3>
                <span class="mt-3 block h-0.5 w-10 bg-lime-500" aria-hidden="true"></span>
                <ul class="mt-6 grid gap-3 text-small">
                    @php
                        $quickLinks = [['About Us', route('about')], ['Our Services', route('services.index')], ['Portfolio', route('portfolio.index')], ['Shop', route('shop.index')]];
                        if (\Illuminate\Support\Facades\Schema::hasTable('posts') && \App\Models\Post::query()->live()->exists()) {
                            $quickLinks[] = ['News', route('news.index')];
                        }
                        $quickLinks[] = ['Contact', route('contact')];
                    @endphp
                    @foreach ($quickLinks as [$label, $href])
                        <li>
                            <a wire:navigate href="{{ $href }}" class="wb-footer-link inline-flex items-center gap-2 transition-colors hover:text-lime-400">
                                <x-icons.wb name="arrow" class="h-3 w-3 text-lime-500" />
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Services --}}
            <div>
                <h3 class="font-display text-[1.05rem] font-bold text-white">Services</h3>
                <span class="mt-3 block h-0.5 w-10 bg-lime-500" aria-hidden="true"></span>
                <ul class="mt-6 grid gap-3 text-small">
                    @foreach ([['Software Development', route('services.software')], ['IT & Networking', route('services.networking')], ['Starlink Solutions', route('services.starlink')], ['CCTV & Intercom', route('services.cctv')], ['Technology Products', route('shop.index')]] as [$label, $href])
                        <li>
                            <a wire:navigate href="{{ $href }}" class="wb-footer-link inline-flex items-center gap-2 transition-colors hover:text-lime-400">
                                <x-icons.wb name="arrow" class="h-3 w-3 text-lime-500" />
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="font-display text-[1.05rem] font-bold text-white">Contact Us</h3>
                <span class="mt-3 block h-0.5 w-10 bg-lime-500" aria-hidden="true"></span>
                <ul class="mt-6 grid gap-5 text-small">
                    <li class="flex gap-3">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white/5 text-lime-400"><x-icons.wb name="pin" class="h-4.5 w-4.5" /></span>
                        <span>
                            <span class="block font-display font-semibold text-white">Address</span>
                            {{ collect([setting('contact.address'), setting('contact.city', 'Juba'), setting('contact.country', 'South Sudan')])->filter()->join(', ') }}
                        </span>
                    </li>

                    @if (setting('contact.hours'))
                        <li class="flex gap-3">
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white/5 text-lime-400"><x-icons.wb name="clock" class="h-4.5 w-4.5" /></span>
                            <span>
                                <span class="block font-display font-semibold text-white">Opening Hours</span>
                                <span class="whitespace-pre-line">{{ setting('contact.hours') }}</span>
                            </span>
                        </li>
                    @endif

                    @if (setting('contact.phone'))
                        <li class="flex gap-3">
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white/5 text-lime-400"><x-icons.wb name="phone" class="h-4.5 w-4.5" /></span>
                            <span>
                                <span class="block font-display font-semibold text-white">Phone</span>
                                <a href="tel:{{ setting('contact.phone') }}" class="font-mono transition-colors hover:text-lime-400">{{ setting('contact.phone') }}</a>
                            </span>
                        </li>
                    @endif

                    @if (setting('contact.email'))
                        <li class="flex gap-3">
                            <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white/5 text-lime-400"><x-icons.wb name="mail" class="h-4.5 w-4.5" /></span>
                            <span>
                                <span class="block font-display font-semibold text-white">Email</span>
                                <a href="mailto:{{ setting('contact.email') }}" class="transition-colors hover:text-lime-400">{{ setting('contact.email') }}</a>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <div class="relative border-t border-white/10">
        <div class="wb-container flex flex-col gap-3 py-6 text-xs sm:flex-row sm:items-center sm:justify-between">
            <p class="text-navy-300">&copy; {{ now()->year }} {{ setting('company.name', 'WestBridge Technologies') }}. All Rights Reserved.</p>
            <p class="font-display font-semibold uppercase tracking-[0.24em] text-lime-400">Connecting Ideas. Building Tomorrow.</p>
            <p class="flex gap-5 text-navy-300">
                <a wire:navigate href="{{ route('legal.privacy') }}" class="transition-colors hover:text-lime-400">Privacy Policy</a>
                <a wire:navigate href="{{ route('legal.terms') }}" class="transition-colors hover:text-lime-400">Terms</a>
            </p>
        </div>
    </div>
</footer>
