<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LeadCaptured;
use App\Models\User;
use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Notifies everyone who can act on a lead. Roles decide the recipient list, so
 * hiring a second salesperson changes nothing in code.
 */
class NotifyTeamOfLead
{
    public function handle(LeadCaptured $event): void
    {
        $recipients = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Administrator', 'Sales']))
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new NewLeadNotification($event->lead));
    }
}
