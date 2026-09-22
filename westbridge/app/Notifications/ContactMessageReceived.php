<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ContactMessage $message) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            // Deliberately generic: nothing the sender typed is echoed back,
            // so this email cannot carry someone else's words to a stranger.
            ->subject('We received your message')
            ->greeting('Thank you for contacting us.')
            ->line('We have received your message and will reply shortly.')
            ->line('If you did not send a message to us, you can ignore this email.')
            ->salutation(setting('company.name', 'WestBridge Technologies'));
    }
}
