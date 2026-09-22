<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\ContactMessage;
use App\Notifications\ContactMessageReceived;
use App\Services\Crm\LeadService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $subject = '';
    public string $message = '';

    public string $website = '';  // honeypot
    public int $loadedAt = 0;

    public bool $submitted = false;

    public function mount(): void
    {
        $this->loadedAt = time();

        // Arriving from a product page ("Ask about this product").
        $product = trim((string) request()->query('product', ''));
        if ($product !== '' && mb_strlen($product) <= 150) {
            $this->subject = 'Product enquiry: '.$product;
        }
    }

    /** @return array<string, array<int, string>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            // The DNS check catches typos like "gmial.com" on the live site; tests
            // skip it so they never depend on the internet.
            'email' => ['required', app()->runningUnitTests() ? 'email:rfc' : 'email:rfc,dns', 'max:180'],
            'phone' => ['nullable', 'string', 'min:7', 'max:32'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    public function updated(string $field): void
    {
        $this->validateOnly($field);
    }

    public function submit(LeadService $leads): void
    {
        if ($this->website !== '' || (time() - $this->loadedAt) < 3) {
            $this->submitted = true;

            return;
        }

        $key = 'contact:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('form', 'Too many messages from this connection. Please try again shortly.');

            return;
        }

        $data = $this->validate();
        RateLimiter::hit($key, 3600);

        // The message and its lead are saved together or not at all.
        $message = DB::transaction(function () use ($data, $leads): ContactMessage {
            $message = ContactMessage::query()->create([
                ...$data,
                'phone' => $data['phone'] ?: null,
                'subject' => $data['subject'] ?: null,
                'source_page' => mb_substr((string) url()->previous(), 0, 255),
                'ip_address' => request()->ip(),
            ]);

            $leads->captureFromContact($message);

            return $message;
        });

        // The automatic "we received your message" reply goes to whatever
        // address was typed, so it is capped at one per address per day -
        // the form cannot be used to flood someone else's inbox.
        $replyKey = 'contact-reply:'.sha1(mb_strtolower($message->email));
        if (! RateLimiter::tooManyAttempts($replyKey, 1)) {
            RateLimiter::hit($replyKey, 86400);
            Notification::route('mail', $message->email)
                ->notify(new ContactMessageReceived($message));
        }

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
