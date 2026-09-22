<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\Product;
use App\Models\WhatsappClick;
use Database\Seeders\SettingSeeder;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(SettingSeeder::class);
});

function product(array $attributes = []): Product
{
    return Product::query()->create([
        'name' => 'Starlink Standard Kit',
        'slug' => 'starlink-standard-kit',
        'price' => 499,
        'currency' => 'USD',
        'stock_status' => 'in_stock',
        'is_published' => true,
        ...$attributes,
    ]);
}

it('shows an honest empty state when nothing is listed', function (): void {
    get('/shop')->assertOk()->assertSeeText('Our catalogue is being added.');
});

it('hides unpublished products', function (): void {
    product(['is_published' => false]);

    get('/shop')->assertDontSeeText('Starlink Standard Kit');
    get('/product/starlink-standard-kit')->assertNotFound();
});

it('falls back to the contact page when no WhatsApp number is set', function (): void {
    product();

    get('/product/starlink-standard-kit')->assertOk()->assertSeeText('Ask about this product');
    get('/product/starlink-standard-kit/whatsapp')->assertRedirect(route('contact', ['product' => 'Starlink Standard Kit']));
});

it('opens WhatsApp with the product named and counts the tap', function (): void {
    setting_service()->set('contact.whatsapp', '+211 912 345 678');
    product();

    get('/product/starlink-standard-kit')->assertOk()->assertSeeText('Order on WhatsApp');

    $response = get('/product/starlink-standard-kit/whatsapp');
    $response->assertRedirect();
    expect($response->headers->get('Location'))
        ->toStartWith('https://wa.me/211912345678?text=')
        ->toContain(rawurlencode('Starlink Standard Kit'));

    expect(WhatsappClick::query()->count())->toBe(1);
});

it('says price on request when prices are switched off', function (): void {
    setting_service()->set('shop.show_prices', '0');
    product();

    get('/shop')->assertSeeText('Price on request')->assertDontSeeText('USD 499.00');
});

it('retires the checkout addresses with redirects', function (): void {
    get('/cart')->assertRedirect('/shop');
    get('/checkout')->assertRedirect('/shop');
});

it('finds products in site search', function (): void {
    product();

    get('/search?q=starlink')->assertOk()->assertSeeText('Starlink Standard Kit');
});
