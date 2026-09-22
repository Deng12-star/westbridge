<div class="mx-auto max-w-2xl">

    @if ($submitted)
        {{-- Success state. The reference is the thing they will quote back. --}}
        <div class="rounded-sm border border-paper-300 bg-white p-8 text-center sm:p-10">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-lime-100 text-lime-700">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>

            <h2 class="mt-5 text-h2">Request received.</h2>

            <p class="mx-auto mt-3 max-w-md text-paper-600">
                A member of our team will be in touch within one business day.
            </p>

            @if ($reference)
                <p class="mt-5 inline-block rounded-xs bg-paper-100 px-4 py-2.5">
                    <span class="text-small text-paper-600">Your reference</span>
                    <span class="ml-2 font-mono font-medium text-navy-700">{{ $reference }}</span>
                </p>
            @endif

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a wire:navigate href="{{ route('home') }}"><x-ui.button variant="outline" size="sm">Back to home</x-ui.button></a>
                @if (setting('contact.whatsapp'))
                    <a href="{{ whatsapp_url('Hello WestBridge, I have just submitted request '.$reference) }}" target="_blank" rel="noopener">
                        <x-ui.button size="sm">Continue on WhatsApp</x-ui.button>
                    </a>
                @endif
            </div>
        </div>
    @else
        <form wire:submit="submit" class="rounded-sm border border-paper-300 bg-white p-6 sm:p-8">

            {{-- Progress --}}
            <div class="mb-8">
                <div class="flex items-center gap-2">
                    @foreach ([1 => 'What you need', 2 => 'Scope', 3 => 'Your details'] as $number => $label)
                        <div class="flex flex-1 flex-col gap-2">
                            <div @class([
                                'h-1 rounded-full transition-colors duration-200',
                                'bg-lime-500' => $step >= $number,
                                'bg-paper-200' => $step < $number,
                            ])></div>
                            <span @class([
                                'font-display text-xs font-medium transition-colors',
                                'text-navy-700' => $step >= $number,
                                'text-paper-500' => $step < $number,
                            ])>{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            @error('form')
                <x-ui.alert variant="error" class="mb-6">{{ $message }}</x-ui.alert>
            @enderror

            {{-- Honeypot: off-screen, not display:none, and never announced --}}
            <div class="absolute left-[-9999px]" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            {{-- ---------- STEP 1 ---------- --}}
            <div @class(['grid gap-5', 'hidden' => $step !== 1])>
                <div>
                    <h2 class="text-h3">What do you need built or installed?</h2>
                    <p class="mt-1.5 text-small text-paper-600">Start with the service. You can change it later.</p>
                </div>

                <x-ui.field label="Service" name="service" :required="true">
                    <select
                        id="service"
                        wire:model.blur="service"
                        @class([
                            'h-12 w-full rounded-xs border bg-white px-3.5 text-body text-paper-900 transition-colors',
                            'focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-lime-500/30',
                            'border-status-crit' => $errors->has('service'),
                            'border-paper-300' => ! $errors->has('service'),
                        ])
                    >
                        <option value="">Choose a service…</option>
                        @foreach ($services as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field
                    label="Describe the project"
                    name="description"
                    :required="true"
                    help="What problem should this solve, who will use it, and anything already in place."
                >
                    <x-ui.textarea name="description" wire:model.blur="description" rows="6"
                        placeholder="We run a school with about 800 students and currently track fees in Excel…" />
                </x-ui.field>

                <div class="flex justify-end">
                    <x-ui.button wire:click="nextStep" type="button">
                        Continue
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3.5L10.5 8L6 12.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </x-ui.button>
                </div>
            </div>

            {{-- ---------- STEP 2 ---------- --}}
            <div @class(['grid gap-5', 'hidden' => $step !== 2])>
                <div>
                    <h2 class="text-h3">Roughly what scope are we working to?</h2>
                    <p class="mt-1.5 text-small text-paper-600">Both fields are optional — a range is enough to point us the right way.</p>
                </div>

                <x-ui.field label="Budget range" name="budget_range">
                    <select id="budget_range" wire:model.blur="budget_range"
                        class="h-12 w-full rounded-xs border border-paper-300 bg-white px-3.5 text-body text-paper-900 transition-colors focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-lime-500/30">
                        <option value="">Prefer not to say</option>
                        @foreach ($budgets as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Preferred timeline" name="timeline">
                    <select id="timeline" wire:model.blur="timeline"
                        class="h-12 w-full rounded-xs border border-paper-300 bg-white px-3.5 text-body text-paper-900 transition-colors focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-lime-500/30">
                        <option value="">Not sure yet</option>
                        @foreach ($timelines as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </x-ui.field>

                <x-ui.field label="Attachment" name="attachment" help="Specification, tender document, floor plan or photo. PDF, Word, Excel or image, up to 10MB.">
                    <div class="flex items-center gap-3">
                        <label for="attachment" class="inline-flex h-12 cursor-pointer items-center gap-2 rounded-xs border-[1.5px] border-paper-300 px-4 font-display text-small font-medium text-navy-700 transition-colors hover:bg-paper-100">
                            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 11V3M5 6l3-3 3 3M3 12v1h10v-1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Choose a file
                        </label>
                        <input type="file" id="attachment" wire:model="attachment" class="sr-only">

                        <div wire:loading wire:target="attachment" class="text-small text-paper-600">Uploading…</div>

                        @if ($attachment && ! $errors->has('attachment'))
                            <span class="truncate text-small text-paper-600">{{ $attachment->getClientOriginalName() }}</span>
                        @endif
                    </div>
                </x-ui.field>

                <div class="flex justify-between gap-3">
                    <x-ui.button wire:click="previousStep" type="button" variant="ghost">Back</x-ui.button>
                    <x-ui.button wire:click="nextStep" type="button">
                        Continue
                        <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3.5L10.5 8L6 12.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </x-ui.button>
                </div>
            </div>

            {{-- ---------- STEP 3 ---------- --}}
            <div @class(['grid gap-5', 'hidden' => $step !== 3])>
                <div>
                    <h2 class="text-h3">How should we reach you?</h2>
                    <p class="mt-1.5 text-small text-paper-600">We reply within one business day.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Full name" name="name" :required="true">
                        <x-ui.input name="name" wire:model.blur="name" autocomplete="name" />
                    </x-ui.field>

                    <x-ui.field label="Company or organisation" name="company">
                        <x-ui.input name="company" wire:model.blur="company" autocomplete="organization" />
                    </x-ui.field>

                    <x-ui.field label="Phone" name="phone" :required="true" help="Include the country code, e.g. +211">
                        <x-ui.input name="phone" type="tel" wire:model.blur="phone" autocomplete="tel" placeholder="+211 " />
                    </x-ui.field>

                    <x-ui.field label="Email" name="email" :required="true">
                        <x-ui.input name="email" type="email" wire:model.blur="email" autocomplete="email" />
                    </x-ui.field>
                </div>

                <div class="flex flex-col-reverse justify-between gap-3 sm:flex-row">
                    <x-ui.button wire:click="previousStep" type="button" variant="ghost">Back</x-ui.button>

                    <x-ui.button type="submit" size="lg" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit">Send request</span>
                        <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" opacity="0.3"/>
                                <path d="M14 8a6 6 0 00-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Sending…
                        </span>
                    </x-ui.button>
                </div>
            </div>
        </form>
    @endif
</div>
