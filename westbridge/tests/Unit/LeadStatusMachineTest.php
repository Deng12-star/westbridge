<?php

declare(strict_types=1);

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Services\Crm\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows only the transitions in the approved pipeline', function (): void {
    expect(LeadStatus::New->canTransitionTo(LeadStatus::Contacted))->toBeTrue()
        ->and(LeadStatus::New->canTransitionTo(LeadStatus::Won))->toBeFalse()
        ->and(LeadStatus::Proposal->canTransitionTo(LeadStatus::Won))->toBeTrue()
        ->and(LeadStatus::Won->isTerminal())->toBeTrue()
        ->and(LeadStatus::Lost->isTerminal())->toBeTrue();
});

it('throws rather than silently allowing an illegal move', function (): void {
    $lead = Lead::query()->create([
        'reference' => 'WB-LEAD-2026-TEST01',
        'name' => 'Test',
        'source' => 'manual',
        'status' => LeadStatus::New,
    ]);

    app(LeadService::class)->transitionTo($lead, LeadStatus::Won);
})->throws(InvalidArgumentException::class);

it('records every status change as a system note', function (): void {
    $lead = Lead::query()->create([
        'reference' => 'WB-LEAD-2026-TEST02',
        'name' => 'Test',
        'source' => 'manual',
        'status' => LeadStatus::New,
    ]);

    $service = app(LeadService::class);
    $service->transitionTo($lead, LeadStatus::Contacted);
    $service->transitionTo($lead, LeadStatus::Qualified);

    expect($lead->refresh()->status)->toBe(LeadStatus::Qualified)
        ->and($lead->notes()->where('is_system', true)->count())->toBe(2)
        ->and($lead->last_contacted_at)->not->toBeNull();
});

it('stamps closed_at and a reason when a lead is lost', function (): void {
    $lead = Lead::query()->create([
        'reference' => 'WB-LEAD-2026-TEST03',
        'name' => 'Test',
        'source' => 'manual',
        'status' => LeadStatus::New,
    ]);

    app(LeadService::class)->transitionTo($lead, LeadStatus::Lost, reason: 'Went with an existing supplier.');

    expect($lead->refresh()->closed_at)->not->toBeNull()
        ->and($lead->lost_reason)->toBe('Went with an existing supplier.');
});
