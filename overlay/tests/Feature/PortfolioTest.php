<?php

declare(strict_types=1);

use App\Models\Project;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(ProjectSeeder::class);
});

it('lists every project on the portfolio page', function (): void {
    get('/portfolio')
        ->assertOk()
        ->assertSeeText('Dream Bridge Payroll System')
        ->assertSeeText('myloan - Loan Management System')
        ->assertSeeText('DB Suk Online Store');
});

it('renders a case study', function (): void {
    get('/portfolio/dream-bridge-payroll-system')
        ->assertOk()
        ->assertSeeText('What the system does')
        ->assertSee('https://dreambridge-payroll.com', false);
});

it('hides a live link that is switched off', function (): void {
    get('/portfolio/db-suk-online-store')
        ->assertOk()
        ->assertDontSee('Visit the live system');
});

it('returns 404 for an unknown project', function (): void {
    get('/portfolio/nope')->assertNotFound();
});

it('shows Our Projects on the homepage once three exist', function (): void {
    get('/')->assertOk()->assertSeeText('Systems we have built');
});

it('hides Our Projects when there are fewer than three', function (): void {
    Project::query()->where('slug', 'db-suk-online-store')->delete();

    get('/')->assertOk()->assertDontSeeText('Systems we have built');
});

// Blocker 7
it('never brings back a project deleted in the admin', function (): void {
    Project::query()->where('slug', 'db-suk-online-store')->delete();

    seed(ProjectSeeder::class);
    seed(ProjectSeeder::class);

    expect(Project::query()->where('slug', 'db-suk-online-store')->exists())->toBeFalse();
    get('/portfolio/db-suk-online-store')->assertNotFound();
});

it('shows an empty portfolio when every project is deleted, not the starter content', function (): void {
    Project::query()->delete();

    get('/portfolio')->assertOk()->assertDontSeeText('Dream Bridge Payroll System');
});
