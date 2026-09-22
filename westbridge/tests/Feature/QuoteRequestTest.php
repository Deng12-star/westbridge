<?php

declare(strict_types=1);

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\ServiceInterest;
use App\Livewire\QuoteRequestForm;
use App\Models\Lead;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use App\Notifications\QuoteRequestReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\get;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(Database\Seeders\RoleSeeder::class);
    seed(Database\Seeders\SettingSeeder::class);
    seed(Database\Seeders\PageSeeder::class);
    Notification::fake();
});

/** This is the Phase 2 acceptance gate, as a test. */
it('turns a quote submission into a lead with the right service', function (): void {
    Livewire::test(QuoteRequestForm::class)
        ->set('loadedAt', time() - 30)
        ->set('service', ServiceInterest::Starlink->value)
        ->set('description', 'We need Starlink installed at our field office outside Juba, plus the internal network.')
        ->call('nextStep')
        ->set('budget_range', '5k_15k')
        ->set('timeline', 'within_month')
        ->call('nextStep')
        ->set('name', 'Grace Lado')
        ->set('company', 'Example Organisation')
        ->set('phone', '+211912345678')
        ->set('email', 'grace@example.com')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $request = QuoteRequest::query()->sole();

    expect($request->service)->toBe(ServiceInterest::Starlink)
        ->and($request->reference)->toStartWith('WB-REQ-');

    $lead = Lead::query()->sole();

    expect($lead->source)->toBe(LeadSource::QuoteForm)
        ->and($lead->status)->toBe(LeadStatus::New)
        ->and($lead->service_interest)->toBe(ServiceInterest::Starlink)
        ->and($lead->sourceable->is($request))->toBeTrue()
        ->and($lead->reference)->toStartWith('WB-LEAD-')
        ->and($lead->notes)->toHaveCount(1);
});

it('pre-selects the service when arriving from a service page', function (): void {
    Livewire::withQueryParams(['service' => 'starlink'])
        ->test(QuoteRequestForm::class)
        ->assertSet('service', ServiceInterest::Starlink->value);
});

it('notifies the sales team and acknowledges the customer', function (): void {
    $sales = User::factory()->create(['is_active' => true]);
    $sales->assignRole('Sales');

    submitValidQuote();

    Notification::assertSentTo($sales, NewLeadNotification::class);
    Notification::assertSentOnDemand(QuoteRequestReceived::class);
});

it('refuses to advance past step one without a description', function (): void {
    Livewire::test(QuoteRequestForm::class)
        ->set('service', ServiceInterest::Website->value)
        ->set('description', 'too short')
        ->call('nextStep')
        ->assertHasErrors(['description'])
        ->assertSet('step', 1);
});

it('silently discards a submission that trips the honeypot', function (): void {
    Livewire::test(QuoteRequestForm::class)
        ->set('loadedAt', time() - 30)
        ->set('service', ServiceInterest::Website->value)
        ->set('description', 'A perfectly reasonable description of a website project we would like built.')
        ->set('name', 'Bot')
        ->set('phone', '+211912345678')
        ->set('email', 'bot@example.com')
        ->set('website', 'http://spam.example')   // hidden field, humans never see it
        ->call('submit')
        ->assertSet('submitted', true);

    // Told nothing, saved nothing.
    expect(QuoteRequest::query()->count())->toBe(0)
        ->and(Lead::query()->count())->toBe(0);
});

it('discards a submission completed faster than a human could read it', function (): void {
    Livewire::test(QuoteRequestForm::class)
        ->set('loadedAt', time())   // submitted the same second the page loaded
        ->set('service', ServiceInterest::Website->value)
        ->set('description', 'A perfectly reasonable description of a website project we would like built.')
        ->set('name', 'Bot')
        ->set('phone', '+211912345678')
        ->set('email', 'bot@example.com')
        ->call('submit');

    expect(QuoteRequest::query()->count())->toBe(0);
});

it('stores attachments on the private disk, never in public storage', function (): void {
    Storage::fake('local');

    Livewire::test(QuoteRequestForm::class)
        ->set('loadedAt', time() - 30)
        ->set('service', ServiceInterest::Erp->value)
        ->set('description', 'We have attached the tender document describing the system we need built.')
        ->set('attachment', UploadedFile::fake()->create('tender.pdf', 400, 'application/pdf'))
        ->set('name', 'Grace Lado')
        ->set('phone', '+211912345678')
        ->set('email', 'grace@example.com')
        ->call('submit')
        ->assertHasNoErrors();

    $request = QuoteRequest::query()->sole();

    expect($request->attachment_name)->toBe('tender.pdf')
        ->and($request->attachment_path)->toStartWith('quote-attachments/');

    Storage::disk('local')->assertExists($request->attachment_path);
});

it('rejects an executable disguised as an attachment', function (): void {
    // Attachments are validated on step 2 of the form.
    Livewire::test(QuoteRequestForm::class)
        ->set('step', 2)
        ->set('attachment', UploadedFile::fake()->create('payload.php', 10))
        ->call('nextStep')
        ->assertHasErrors(['attachment']);
});

it('no longer serves the quote page directly', function (): void {
    get(route('quote'))->assertRedirect('/contact');
});

function submitValidQuote(): void
{
    Livewire::test(QuoteRequestForm::class)
        ->set('loadedAt', time() - 30)
        ->set('service', ServiceInterest::SoftwareDevelopment->value)
        ->set('description', 'We run a school with about 800 students and track fees in spreadsheets today.')
        ->set('name', 'Grace Lado')
        ->set('phone', '+211912345678')
        ->set('email', 'grace@example.com')
        ->call('submit')
        ->assertHasNoErrors();
}
