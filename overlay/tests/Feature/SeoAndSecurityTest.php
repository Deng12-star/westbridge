<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Product;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('serves a sitemap listing products and projects', function (): void {
    $this->seed(Database\Seeders\ProjectSeeder::class);
    Product::query()->create(['name' => 'Router X', 'slug' => 'router-x', 'currency' => 'USD', 'stock_status' => 'in_stock', 'is_published' => true]);

    get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('/product/router-x', false)
        ->assertSee('/portfolio/', false);
});

it('keeps non-production sites out of search engines', function (): void {
    get('/robots.txt')->assertOk()->assertSee('Disallow: /');
});

it('sends security headers', function (): void {
    get('/')->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('never caches admin pages', function (): void {
    $cache = (string) get('/admin/login')->headers->get('Cache-Control');
    expect($cache)->toContain('no-store')->toContain('private');
});

it('publishes valid organisation JSON-LD', function (): void {
    $html = get('/')->getContent();
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

    $data = json_decode($m[1] ?? '', true);
    expect($data)->toBeArray()->and($data['@type'])->toBe('Organization');
});

it('serves the public API', function (): void {
    Product::query()->create(['name' => 'Router X', 'slug' => 'router-x', 'price' => 90, 'currency' => 'USD', 'stock_status' => 'in_stock', 'is_published' => true]);

    getJson('/api/v1/products')->assertOk()->assertJsonPath('data.0.slug', 'router-x');
    getJson('/api/v1/products/router-x')->assertOk()->assertJsonPath('data.name', 'Router X');
    getJson('/api/v1/products/nope')->assertNotFound();
    getJson('/api/v1/projects')->assertOk();
    getJson('/api/v1/categories')->assertOk();
    getJson('/api/v1/site')->assertOk()->assertJsonPath('data.name', 'WestBridge Technologies');
});

it('protects API writes', function (): void {
    $this->postJson('/api/v1/products', ['name' => 'X'])->assertStatus(class_exists(\Laravel\Sanctum\Sanctum::class) ? 401 : 405);
});
