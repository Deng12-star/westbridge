<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Stub.
 *
 * WhatsApp and SMS are the channels that actually reach people here, but they
 * need a provider account. The channel exists now so notifications can declare
 * `via: ['mail', 'whatsapp']` from the start; wiring a provider later is a
 * change to this one class and nothing else.
 *
 * A notification opts in by implementing toWhatsApp(): string.
 */
class WhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $to = method_exists($notifiable, 'routeNotificationForWhatsApp')
            ? $notifiable->routeNotificationForWhatsApp($notification)
            : ($notifiable->phone ?? null);

        if (blank($to)) {
            return;
        }

        // No provider configured yet — record the intent so nothing is lost
        // silently and the volume can be sized before choosing a provider.
        Log::channel(config('logging.default'))->info('WhatsApp notification pending provider', [
            'to' => $to,
            'notification' => $notification::class,
            'body' => $notification->toWhatsApp($notifiable),
        ]);
    }
}
