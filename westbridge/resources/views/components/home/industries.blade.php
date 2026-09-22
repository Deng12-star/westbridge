{{--
    Sits where the reference site shows "Our Projects". Until real case studies
    exist, the honest version of that slot is who the work is for. When three or
    more projects are published, the selected-work block takes over.
--}}
<section class="relative overflow-hidden bg-navy-900 text-white">
    <div class="pointer-events-none absolute -right-40 -top-40 h-[480px] w-[480px] rounded-full bg-lime-500/10 blur-3xl" aria-hidden="true"></div>

    <div class="wb-container relative py-20 md:py-28">
        <div class="mx-auto max-w-2xl text-center">
            <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-400">Who We Work With</p>
            <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] text-white md:text-[2.5rem]">Technology for every sector</h2>
            <p class="mt-4 text-navy-200">Every sector runs on the same foundations - reliable systems, a working network and equipment that is supported after it is delivered.</p>
        </div>

        <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (config('westbridge.home.industries') as $industry)
                <div class="wb-industry group relative overflow-hidden rounded-sm border border-white/10 bg-white/[0.03] p-7 transition-all duration-300 hover:-translate-y-1 hover:border-lime-500/40">
                    <span class="wb-industry-glow pointer-events-none absolute inset-0" aria-hidden="true"></span>
                    <span class="relative flex h-12 w-12 items-center justify-center rounded-sm bg-white/5 text-lime-400 transition-colors duration-300 group-hover:bg-lime-500 group-hover:text-navy-900">
                        <x-icons.wb :name="$industry['icon']" class="h-6 w-6" />
                    </span>
                    <h3 class="relative mt-5 font-display text-h3 font-bold text-white">{{ $industry['title'] }}</h3>
                    <p class="relative mt-2 text-small text-navy-300">{{ $industry['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
