<?php

declare(strict_types=1);

/**
 * The official brand colours are sampled from the logo file and must not drift.
 * If someone "adjusts" the navy in the Tailwind theme, this fails.
 */
it('keeps the official brand colours in the Tailwind theme', function (): void {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('--color-navy-700: #1C2B4F')
        ->toContain('--color-lime-500: #89C726');
});

it('keeps the document numbering format agreed in the blueprint', function (): void {
    expect(config('westbridge.documents.prefix'))->toBe('WB')
        ->and(config('westbridge.documents.padding'))->toBe(6)
        ->and(config('westbridge.documents.series'))->toMatchArray([
            'order' => 'ORD',
            'quotation' => 'QTN',
            'invoice' => 'INV',
            'receipt' => 'RCP',
        ]);
});
