<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Auto-acknowledgement to the customer. Sets an expectation for when a human
 * will reply — journey J1 is lost at exactly this step when nothing arrives.
 */
class QuoteRequestReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly QuoteRequest $request) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We received your request — '.$this->request->reference)
            ->greeting("Thank you, {$this->request->name}.")
            ->line('We have received your request and a member of our team will be in touch within one business day.')
            ->line("Your reference is **{$this->request->reference}** — quote it if you contact us before we reach you.")
            ->line("Service: {$this->request->service->label()}")
            ->salutation(setting('company.name', 'WestBridge Technologies'));
    }
}
