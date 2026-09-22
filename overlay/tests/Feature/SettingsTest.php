<?php

declare(strict_types=1);

use App\Services\Platform\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Database\Seeders\SettingSeeder::class);
    $this->settings = app(SettingsService::class);
});

it('treats a blank setting as absent so templates can hide the element', function (): void {
    expect($this->settings->get('contact.phone'))->toBeNull()
        ->and($this->settings->get('contact.phone', 'fallback'))->toBe('fallback');
});

it('hides every social icon until its URL is configured', function (): void {
    expect(active_socials())->toBeEmpty();

    $this->settings->set('social.linkedin', 'https://www.linkedin.com/company/example');

    expect(active_socials())->toHaveKey('linkedin')->toHaveCount(1);
});

it('builds a wa.me link with only digits', function (): void {
    expect(whatsapp_url())->toBeNull();

    $this->settings->set('contact.whatsapp', '+211 912 345 678');

    expect(whatsapp_url())->toStartWith('https://wa.me/211912345678?text=');
});

it('flushes its cache when a setting changes', function (): void {
    expect($this->settings->get('company.name'))->toBe('WestBridge Technologies');

    $this->settings->set('company.name', 'WestBridge Technologies Ltd');

    expect($this->settings->get('company.name'))->toBe('WestBridge Technologies Ltd');
});
