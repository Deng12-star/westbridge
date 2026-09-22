<div>
    @if ($submitted)
        <div class="rounded-sm border border-paper-300 bg-white p-8 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-lime-100 text-lime-700">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <h2 class="mt-4 text-h3">Message sent.</h2>
            <p class="mx-auto mt-2 max-w-sm text-small text-paper-600">Thank you — we will reply shortly.</p>
        </div>
    @else
        <form wire:submit="submit" class="grid gap-5">
            @error('form')
                <x-ui.alert variant="error">{{ $message }}</x-ui.alert>
            @enderror

            <div class="absolute left-[-9999px]" aria-hidden="true">
                <label for="c-website">Website</label>
                <input type="text" id="c-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.field label="Full name" name="name" :required="true">
                    <x-ui.input name="name" wire:model.blur="name" autocomplete="name" />
                </x-ui.field>

                <x-ui.field label="Email" name="email" :required="true">
                    <x-ui.input name="email" type="email" wire:model.blur="email" autocomplete="email" />
                </x-ui.field>

                <x-ui.field label="Phone" name="phone" help="Optional, but usually the fastest way to reach you.">
                    <x-ui.input name="phone" type="tel" wire:model.blur="phone" autocomplete="tel" placeholder="+211 " />
                </x-ui.field>

                <x-ui.field label="Subject" name="subject">
                    <x-ui.input name="subject" wire:model.blur="subject" />
                </x-ui.field>
            </div>

            <x-ui.field label="Message" name="message" :required="true">
                <x-ui.textarea name="message" wire:model.blur="message" rows="6" />
            </x-ui.field>

            <div class="flex items-center justify-between gap-4">
                <p class="text-xs text-paper-600">We reply within one business day.</p>
                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Send message</span>
                    <span wire:loading wire:target="submit">Sending…</span>
                </x-ui.button>
            </div>
        </form>
    @endif
</div>
