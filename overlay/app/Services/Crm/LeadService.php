<?php

declare(strict_types=1);

namespace App\Services\Crm;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\ServiceInterest;
use App\Events\LeadCaptured;
use App\Events\LeadStatusChanged;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Models\LeadNote;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * The single entry point for every enquiry.
 *
 * Contact forms, quote requests and product enquiries all land here, so there
 * is exactly one place where a lead comes into existence and exactly one
 * notification path. The web layer, the admin and the future API all call
 * these same methods.
 */
class LeadService
{
    public function captureFromQuote(QuoteRequest $request): Lead
    {
        return DB::transaction(function () use ($request): Lead {
            $lead = $this->create([
                'name' => $request->name,
                'company' => $request->company,
                'phone' => $request->phone,
                'email' => $request->email,
                'service_interest' => $request->service?->value,
                'message' => $request->description,
                'source' => LeadSource::QuoteForm,
                'sourceable_type' => $request->getMorphClass(),
                'sourceable_id' => $request->id,
            ]);

            $this->note($lead, sprintf(
                'Quote request %s received. Budget: %s. Timeline: %s.%s',
                $request->reference,
                $request->budget_range?->label() ?? 'not stated',
                $request->timeline?->label() ?? 'not stated',
                $request->hasAttachment() ? ' Attachment included.' : '',
            ), system: true);

            return $lead;
        });
    }

    public function captureFromContact(ContactMessage $message): Lead
    {
        return DB::transaction(function () use ($message): Lead {
            $lead = $this->create([
                'name' => $message->name,
                'phone' => $message->phone,
                'email' => $message->email,
                'message' => $message->message,
                'source' => LeadSource::ContactForm,
                'sourceable_type' => $message->getMorphClass(),
                'sourceable_id' => $message->id,
            ]);

            $this->note($lead, 'Contact form message received.', system: true);

            return $lead;
        });
    }

    /**
     * Product enquiries (Phase 4) reuse this path so "Request Product
     * Information" produces a lead identical in shape to every other.
     *
     * @param  array{name: string, phone?: ?string, email?: ?string, message?: ?string}  $details
     */
    public function captureFromProduct(array $details, ?string $productType = null, ?int $productId = null): Lead
    {
        return DB::transaction(function () use ($details, $productType, $productId): Lead {
            $lead = $this->create([
                ...$details,
                'service_interest' => ServiceInterest::Hardware->value,
                'source' => LeadSource::ProductInquiry,
                'sourceable_type' => $productType,
                'sourceable_id' => $productId,
            ]);

            $this->note($lead, 'Product enquiry received from the shop.', system: true);

            return $lead;
        });
    }

    public function assign(Lead $lead, User $user, ?User $by = null): Lead
    {
        $lead->update(['assigned_to' => $user->id]);

        $this->note(
            $lead,
            sprintf('Assigned to %s%s.', $user->name, $by ? " by {$by->name}" : ''),
            $by,
            system: true,
        );

        return $lead->refresh();
    }

    /**
     * Move a lead through the pipeline. Illegal transitions throw — the
     * status machine is the rule, the dropdown is only its presentation.
     */
    public function transitionTo(Lead $lead, LeadStatus $target, ?User $by = null, ?string $reason = null): Lead
    {
        $from = $lead->status;

        if ($from === $target) {
            return $lead;
        }

        if (! $from->canTransitionTo($target)) {
            throw new InvalidArgumentException(
                "A lead cannot move from {$from->label()} to {$target->label()}."
            );
        }

        DB::transaction(function () use ($lead, $from, $target, $by, $reason): void {
            $lead->update([
                'status' => $target,
                'closed_at' => $target->isTerminal() ? now() : null,
                'lost_reason' => $target === LeadStatus::Lost ? $reason : null,
                'last_contacted_at' => $target === LeadStatus::Contacted ? now() : $lead->last_contacted_at,
            ]);

            $this->note(
                $lead,
                sprintf('Status changed from %s to %s.%s', $from->label(), $target->label(), $reason ? " Reason: {$reason}" : ''),
                $by,
                system: true,
            );

            LeadStatusChanged::dispatch($lead, $from, $target);
        });

        return $lead->refresh();
    }

    public function note(Lead $lead, string $body, ?User $by = null, bool $system = false): LeadNote
    {
        return $lead->notes()->create([
            'body' => $body,
            'user_id' => $by?->id,
            'is_system' => $system,
        ]);
    }

    /** @param  array<string, mixed>  $attributes */
    private function create(array $attributes): Lead
    {
        $lead = Lead::query()->create([
            ...$attributes,
            'reference' => $this->reference(),
            'status' => LeadStatus::New,
        ]);

        LeadCaptured::dispatch($lead);

        return $lead;
    }

    /**
     * Human-quotable reference for a lead: WB-LEAD-2026-A7K3QP.
     * Not a gapless sequence — leads are not financial documents, so a random
     * suffix avoids leaking enquiry volume to anyone who receives one.
     */
    private function reference(): string
    {
        do {
            $reference = sprintf('WB-LEAD-%s-%s', now()->year, Str::upper(Str::random(6)));
        } while (Lead::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
