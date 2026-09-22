<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\BudgetRange;
use App\Enums\ProjectTimeline;
use App\Enums\ServiceInterest;
use App\Models\QuoteRequest;
use App\Notifications\QuoteRequestReceived;
use App\Services\Crm\LeadService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

/**
 * The primary conversion surface on the site.
 *
 * Three steps rather than one long form: asking a stranger for a phone number
 * before they have described their problem is where journey J1 leaks.
 * Details are collected last, once they are already invested.
 */
class QuoteRequestForm extends Component
{
    use WithFileUploads;

    /** Pre-selects the service when arriving from a service page CTA. */
    #[Url(as: 'service', keep: false)]
    public string $serviceSlug = '';

    public int $step = 1;

    // Step 1 — what you need
    public string $service = '';
    public string $description = '';

    // Step 2 — scope
    public string $budget_range = '';
    public string $timeline = '';
    public $attachment = null;

    // Step 3 — who you are
    public string $name = '';
    public string $company = '';
    public string $phone = '';
    public string $email = '';

    /** Honeypot. A human never sees this field; bots fill everything. */
    public string $website = '';
    public int $loadedAt = 0;

    public ?string $reference = null;
    public bool $submitted = false;

    public function mount(): void
    {
        $this->loadedAt = time();

        if ($this->serviceSlug !== '') {
            $this->service = ServiceInterest::fromSlug($this->serviceSlug)?->value ?? '';
        }
    }

    /** @return array<string, string> */
    protected function rules(): array
    {
        return [
            'service' => ['required', 'string', 'in:'.implode(',', array_keys(ServiceInterest::options()))],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'budget_range' => ['nullable', 'string', 'in:'.implode(',', array_keys(BudgetRange::options()))],
            'timeline' => ['nullable', 'string', 'in:'.implode(',', array_keys(ProjectTimeline::options()))],
            // Uploads are restricted by extension AND mime, capped at 10MB.
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'company' => ['nullable', 'string', 'max:160'],
            'phone' => ['required', 'string', 'min:7', 'max:32'],
            // The DNS check catches typos like "gmial.com" on the live site; tests
            // skip it so they never depend on the internet.
            'email' => ['required', app()->runningUnitTests() ? 'email:rfc' : 'email:rfc,dns', 'max:180'],
        ];
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'description.min' => 'A sentence or two more will help us scope this properly.',
            'attachment.mimes' => 'Attach a PDF, Word or Excel document, or an image.',
            'attachment.max' => 'Attachments must be under 10MB. Send larger files by email after we reply.',
            'phone.required' => 'A phone number lets us reach you quickly — this is usually faster than email.',
        ];
    }

    /** Validate on blur, not on every keystroke. */
    public function updated(string $field): void
    {
        if ($field !== 'attachment') {
            $this->validateOnly($field);
        }
    }

    public function nextStep(): void
    {
        $this->validate(match ($this->step) {
            1 => array_intersect_key($this->rules(), array_flip(['service', 'description'])),
            2 => array_intersect_key($this->rules(), array_flip(['budget_range', 'timeline', 'attachment'])),
            default => [],
        });

        $this->step = min($this->step + 1, 3);
    }

    public function previousStep(): void
    {
        $this->step = max($this->step - 1, 1);
        $this->resetErrorBag();
    }

    public function submit(LeadService $leads): void
    {
        // Honeypot and a minimum completion time: a bot fills the hidden field,
        // or submits faster than a person can read the first question.
        if ($this->website !== '' || (time() - $this->loadedAt) < 4) {
            $this->submitted = true;   // fail silently, tell the bot nothing
            $this->reference = null;

            return;
        }

        $key = 'quote:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('form', 'Too many requests from this connection. Please try again in a few minutes, or contact us on WhatsApp.');

            return;
        }

        $data = $this->validate();
        RateLimiter::hit($key, 3600);

        $stored = null;
        $originalName = null;
        $size = null;

        if ($this->attachment) {
            $originalName = $this->attachment->getClientOriginalName();
            $size = $this->attachment->getSize();
            // Private disk, randomised name — never served from public storage.
            $stored = $this->attachment->store('quote-attachments', 'local');
        }

        $request = QuoteRequest::query()->create([
            'reference' => $this->reference(),
            'name' => $data['name'],
            'company' => $data['company'] ?: null,
            'phone' => $data['phone'],
            'email' => $data['email'],
            'service' => $data['service'],
            'description' => $data['description'],
            'budget_range' => $data['budget_range'] ?: null,
            'timeline' => $data['timeline'] ?: null,
            'attachment_path' => $stored,
            'attachment_name' => $originalName,
            'attachment_size' => $size,
            'source_page' => url()->previous(),
            'ip_address' => request()->ip(),
        ]);

        $leads->captureFromQuote($request);

        Notification::route('mail', $request->email)
            ->notify(new QuoteRequestReceived($request));

        $this->reference = $request->reference;
        $this->submitted = true;
    }

    private function reference(): string
    {
        do {
            $reference = sprintf('WB-REQ-%s-%s', now()->year, Str::upper(Str::random(6)));
        } while (QuoteRequest::query()->where('reference', $reference)->exists());

        return $reference;
    }

    public function render()
    {
        return view('livewire.quote-request-form', [
            'services' => ServiceInterest::options(),
            'budgets' => BudgetRange::options(),
            'timelines' => ProjectTimeline::options(),
        ]);
    }
}
