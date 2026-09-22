<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Proposal = 'proposal';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::Proposal => 'Proposal',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    /**
     * Allowed transitions. Enforced in LeadService — an illegal move throws
     * rather than being merely hidden in a dropdown.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Contacted, self::Lost],
            self::Contacted => [self::Qualified, self::Lost],
            self::Qualified => [self::Proposal, self::Lost],
            self::Proposal => [self::Won, self::Lost],
            self::Won, self::Lost => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    /** Colour token used by status pills in admin (Phase 7). */
    public function tone(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Contacted, self::Qualified, self::Proposal => 'warning',
            self::Won => 'success',
            self::Lost => 'neutral',
        };
    }
}
