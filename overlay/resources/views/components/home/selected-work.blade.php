@props(['projects' => []])

{{-- Our Projects. Renders ONLY when there are enough case studies to be
     credible - a strip with one entry costs more trust than no strip at all. --}}
@if (count($projects) >= config('westbridge.content.minimum_case_studies_to_show_block', 3))
    <section class="bg-paper-100">
        <div class="wb-container py-20 md:py-28">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <p class="font-display text-eyebrow font-semibold uppercase tracking-[0.16em] text-lime-700">Our Projects</p>
                    <h2 class="mt-3 text-[2rem] font-bold leading-tight tracking-[-0.02em] md:text-[2.5rem]">Systems we have built</h2>
                    <p class="mt-4 text-paper-600">Payroll, lending and retail - software built for organisations in South Sudan and running on real data.</p>
                </div>
                <a wire:navigate href="{{ route('portfolio.index') }}" class="group inline-flex flex-shrink-0 items-center gap-2 rounded-xs border border-navy-700 px-5 py-3 font-display text-small font-semibold text-navy-700 transition-colors hover:bg-navy-700 hover:text-white">
                    View all projects
                    <x-icons.wb name="arrow" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                </a>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <x-work.project-card :project="$project" />
                @endforeach
            </div>
        </div>
    </section>
@endif
