<?php

declare(strict_types=1);

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(Database\Seeders\SettingSeeder::class);
    seed(Database\Seeders\PageSeeder::class);
});

it('renders every Phase 2 page', function (string $route): void {
    get(route($route))->assertOk();
})->with(['home', 'about', 'services.index', 'contact', 'legal.privacy', 'legal.terms', 'legal.warranty']);

it('sends the retired quote page to contact', function (): void {
    get('/quote')->assertRedirect('/contact')->assertStatus(301);
});

it('renders the homepage without an Our Projects block until there are case studies', function (): void {
    get('/')->assertOk()->assertDontSeeText('Systems we have built');
});

it('survives a page record that does not exist yet', function (): void {
    Page::query()->where('slug', 'about')->delete();

    get(route('about'))->assertOk();
});

it('shows an empty state on a legal page with no body written', function (): void {
    get(route('legal.terms'))
        ->assertOk()
        ->assertSee('This page has not been published yet.');
});

it('serves content from the database, not from the template', function (): void {
    Page::query()->where('slug', 'about')->update(['heading' => 'A different heading entirely']);

    get(route('about'))->assertOk()->assertSee('A different heading entirely');
});
