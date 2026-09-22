{{-- Block 08. Plain claims only — no superlatives, nothing that cannot be
     defended in a meeting. --}}
<section class="bg-white">
    <div class="wb-container py-16 md:py-24">
        <x-ui.section-header
            eyebrow="Why WestBridge"
            title="Built for how things actually work here."
        />

        <div class="mt-10 grid gap-x-10 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Innovation', 'Modern technology chosen for what a business actually needs, not for what is fashionable.'],
                ['Reliability', 'Solutions designed for real operating conditions — intermittent power, variable bandwidth, mixed equipment.'],
                ['Local understanding', 'Built for the South Sudanese market by a team working in it, not adapted from somewhere else.'],
                ['End-to-end', 'Software, hardware and connectivity from one partner, so responsibility never falls between suppliers.'],
                ['Customer support', 'Support continues after the installation or the delivery, not until the invoice is paid.'],
            ] as [$title, $blurb])
                <div class="border-t-2 border-lime-500 pt-4">
                    <h3 class="text-h3">{{ $title }}</h3>
                    <p class="mt-2 text-small text-paper-600">{{ $blurb }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
