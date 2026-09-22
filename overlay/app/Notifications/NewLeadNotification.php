<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the sales team. Queued — a visitor must never wait on an SMTP
 * handshake to see their confirmation screen.
 */
class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Lead $lead) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;

        $mail = (new MailMessage)
            ->subject("New {$lead->source->label()}: {$lead->name}")
            ->greeting('New enquiry')
            ->line("**{$lead->name}**".($lead->company ? " — {$lead->company}" : ''))
            ->line("Reference: {$lead->reference}");

        if ($lead->service_interest) {
            $mail->line("Service: {$lead->service_interest->label()}");
        }

        if ($lead->phone) {
            $mail->line("Phone: {$lead->phone}");
        }

        if ($lead->email) {
            $mail->line("Email: {$lead->email}");
        }

        if ($lead->message) {
            $mail->line('---')->line($lead->message);
        }

        return $mail->line('Open the admin panel to assign and respond.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'reference' => $this->lead->reference,
            'name' => $this->lead->name,
            'source' => $this->lead->source->value,
        ];
    }
}
