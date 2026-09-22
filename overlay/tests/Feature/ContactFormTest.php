<?php

declare(strict_types=1);

use App\Enums\LeadSource;
use App\Livewire\ContactForm;
use App\Models\ContactMessage;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

use function Pest\Laravel\get;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(Database\Seeders\SettingSeeder::class);
    seed(Database\Seeders\PageSeeder::class);
    Notification::fake();
});

it('turns a contact message into a lead', function (): void {
    Livewire::test(ContactForm::class)
        ->set('loadedAt', time() - 30)
        ->set('name', 'Peter Deng')
        ->set('email', 'peter@example.com')
        ->set('phone', '+211911222333')
        ->set('message', 'Do you install office WiFi in Juba town?')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $message = ContactMessage::query()->sole();
    $lead = Lead::query()->sole();

    expect($lead->source)->toBe(LeadSource::ContactForm)
        ->and($lead->sourceable->is($message))->toBeTrue()
        ->and($message->is_read)->toBeFalse();
});

it('requires a real message, not one word', function (): void {
    Livewire::test(ContactForm::class)
        ->set('message', 'hi')
        ->call('submit')
        ->assertHasErrors(['message']);
});

it('renders the contact page without inventing contact details', function (): void {
    // Settings are seeded blank: no phone row, no social icons, no WhatsApp.
    get(route('contact'))
        ->assertOk()
        ->assertSee('Talk to us.')
        ->assertDontSee('wa.me');
});
