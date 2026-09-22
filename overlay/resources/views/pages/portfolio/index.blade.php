<x-layouts.app title="Portfolio" description="Software WestBridge Technologies has built for organisations in South Sudan: payroll, loan management and e-commerce systems.">

    <x-page.hero
        eyebrow="Portfolio"
        title="Systems we have built."
        lede="Each of these is running software, built for a real organisation. Open any project to see what it does and what it is built with."
    />

    <section class="bg-white">
        <div class="wb-container py-16 md:py-24">
            @if ($projects->isEmpty())
                <x-ui.empty-state title="Case studies are on their way." />
            @else
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-work.project-card :project="$project" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

</x-layouts.app>
