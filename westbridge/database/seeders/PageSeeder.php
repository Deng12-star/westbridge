<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * DRAFT COPY.
 *
 * Everything below is structural copy written to give the pages shape and to
 * be edited in the admin panel — not final marketing text. It is deliberately
 * free of anything that cannot be verified: no founding date, no team size, no
 * client numbers, no superlatives, no "leading" or "best in South Sudan".
 *
 * Two blocks are intentionally left NULL because they cannot be written
 * without the client:
 *   - about.story            the actual founding story
 *   - about.why_technology   the company's own position
 * Their sections hide themselves until those blocks are filled.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $this->about();
        $this->simple('services', 'Services', 'Everything from the software to the signal.',
            'Four areas of work, delivered by one team. Most engagements draw on more than one of them.');
        $this->simple('contact', 'Contact', 'Talk to us.',
            'Tell us what you need and we will come back within one business day. For anything urgent, WhatsApp reaches us fastest.');
        $this->simple('quote', 'Request a Quote', 'Tell us what you need.',
            'Three short steps. We reply within one business day with a scoped approach and a written quotation.');

        $this->legal();
    }

    private function about(): void
    {
        $this->page('about', [
            'title' => 'About Us',
            'heading' => 'A technology partner, not a supplier.',
            'lede' => 'WestBridge Technologies builds software, delivers IT infrastructure and connectivity, and supplies technology products to businesses, institutions and organisations in South Sudan.',
            'meta_title' => 'About WestBridge Technologies',
            'meta_description' => 'WestBridge Technologies is a technology company in Juba, South Sudan, building software and delivering IT infrastructure, connectivity and technology products.',
            'blocks' => [
                'who_we_are' => <<<'HTML'
                    <p>WestBridge Technologies is a technology company based in Juba, South Sudan. We work with
                    businesses, institutions, government entities, organisations and individuals — building the
                    systems they run on, installing the infrastructure those systems need, and supplying the
                    equipment that sits on the desk.</p>

                    <p>The name is the idea: a bridge connects two places that were previously separated. Most of
                    the organisations we work with have a clear sense of where they want to get to and no reliable
                    route there. Our work is building that route and keeping it open.</p>
                    HTML,

                'what_we_do' => <<<'HTML'
                    <p>Four areas of work, delivered by one team: software development, IT and networking,
                    connectivity including Starlink, and the supply of technology products.</p>

                    <p>These are usually bought separately, from separate suppliers, and the gaps between them are
                    where projects stall — a system that nobody can reach because the network was never finished, or
                    hardware that arrives and sits in a box. Running all four under one roof means responsibility
                    never falls between two companies.</p>
                    HTML,

                'mission' => 'To give organisations in South Sudan technology they can depend on — built for how they actually work, supported by people they can reach.',

                'vision' => 'A South Sudan where local institutions run on systems built locally, by people who understand the conditions they operate in.',

                'values' => [
                    ['title' => 'Do the unglamorous part', 'body' => 'Cable runs, data migration, staff training. Projects fail on the parts nobody wants to own, so we own them.'],
                    ['title' => 'Say what is true', 'body' => 'If a timeline is unrealistic or a requirement is a bad idea, we say so before the contract, not after.'],
                    ['title' => 'Build for the conditions', 'body' => 'Intermittent power, variable bandwidth, mixed equipment. A solution that only works in ideal conditions is not a solution.'],
                    ['title' => 'Stay after delivery', 'body' => 'The relationship starts at handover. Support that ends when the invoice is paid is not support.'],
                    ['title' => 'Explain the technology', 'body' => 'Clients should understand what they are buying well enough to make their own decisions about it.'],
                    ['title' => 'Transfer the skill', 'body' => 'Wherever we can, we leave the client able to run and extend what we built without us.'],
                ],

                'approach' => [
                    ['title' => 'Understand', 'body' => 'We start with how the work is done today — including the spreadsheets and the paper — before proposing anything.'],
                    ['title' => 'Scope', 'body' => 'A written quotation setting out what is included, what it costs and how long it takes. No moving targets.'],
                    ['title' => 'Build', 'body' => 'Delivered in stages you can see and judge, so nothing is a surprise at the end.'],
                    ['title' => 'Support', 'body' => 'Training at handover, then ongoing support as the organisation and its needs change.'],
                ],

                // Needs the client's own words — the section hides until filled.
                'story' => null,
                'why_technology' => null,
            ],
        ]);
    }

    private function legal(): void
    {
        // Legal text is the client's to provide or have drafted. The pages
        // exist, are routed and are linked from the footer; each one shows an
        // empty state until its body is written. Publishing invented terms of
        // sale or a made-up privacy policy would be worse than an empty page.
        foreach ([
            ['privacy-policy', 'Privacy Policy', 'How we handle your information.'],
            ['terms', 'Terms of Sale & Service', 'The terms that apply to our products and services.'],
            ['warranty-returns', 'Warranty & Returns', 'What is covered, for how long, and how to make a claim.'],
        ] as [$slug, $title, $lede]) {
            $this->page($slug, [
                'title' => $title,
                'heading' => $title,
                'lede' => $lede,
                'blocks' => ['body' => null],
                'is_published' => true,
            ]);
        }
    }

    private function simple(string $slug, string $title, string $heading, string $lede): void
    {
        $this->page($slug, [
            'title' => $title,
            'heading' => $heading,
            'lede' => $lede,
            'blocks' => [],
        ]);
    }

    /** @param array<string, mixed> $attributes */
    private function page(string $slug, array $attributes): void
    {
        // Create-only: once a page exists it belongs to the admin panel, and
        // re-running the seeder must never undo someone's edits.
        Page::query()->firstOrCreate(
            ['slug' => $slug],
            [...$attributes, 'is_system' => true],
        );
    }
}
