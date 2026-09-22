{{--
    The reference site runs a partner-logo strip here. We have no partner
    agreements to show, and using other companies' logos without one would be
    a claim we cannot back - so this lists the technologies we work with, as
    plain words, in the same moving strip. Hover pauses it.
--}}
<section class="border-y border-paper-200 bg-paper-50" aria-label="Technologies we work with">
    <div class="wb-container flex items-center gap-8 py-7">
        <p class="hidden flex-shrink-0 font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-paper-500 md:block">We work with</p>
        <div class="wb-marquee relative flex-1 overflow-hidden">
            <div class="wb-marquee-track flex w-max">
                @foreach ([1, 2] as $pass)
                    @foreach (config('westbridge.home.technologies') as $tech)
                        <span @if ($pass === 2) aria-hidden="true" @endif class="flex items-center gap-12 whitespace-nowrap pr-12 font-display text-lg font-semibold text-navy-300 transition-colors hover:text-navy-700">
                            {{ $tech }}
                            <span class="h-1.5 w-1.5 rounded-full bg-lime-500"></span>
                        </span>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</section>
