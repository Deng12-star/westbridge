<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('renders the homepage with the brand tagline', function (): void {
    get('/')
        ->assertOk()
        ->assertSeeText('Technology That Connects Ideas to Possibilities.')
        ->assertSee('Connecting Ideas. Building Tomorrow.')
        ->assertDontSee('Request a Quote');
});

it('registers every route in the approved sitemap', function (string $name): void {
    expect(route($name, ['slug' => 'x', 'category' => 'x'], false))->toBeString();
})->with([
    'home', 'about', 'contact', 'quote',
    'services.index', 'services.software', 'services.networking', 'services.starlink',
    'portfolio.index', 'projects.index', 'insights.index',
    'shop.index', 'cart', 'checkout', 'order.track', 'search',
    'legal.privacy', 'legal.terms', 'legal.warranty',
]);

it('exposes a branded 404 page rather than the framework default', function (): void {
    get('/a-page-that-does-not-exist')
        ->assertNotFound()
        ->assertSee('We could not find that page.');
});
