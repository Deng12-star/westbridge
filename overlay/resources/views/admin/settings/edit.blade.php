<x-admin.layout title="Settings">
    <div class="mb-6 flex flex-wrap gap-2 border-b border-paper-300">
        @foreach ($groups as $key => $label)
            <a href="{{ route('admin.settings.edit', $key) }}" @class([
                '-mb-px border-b-2 px-4 py-2.5 font-display text-sm font-semibold',
                'border-lime-500 text-navy-700' => $group === $key,
                'border-transparent text-paper-600 hover:text-navy-700' => $group !== $key,
            ])>{{ $label }}</a>
        @endforeach
    </div>

    <div class="max-w-3xl">
        @if ($group === 'contact')
            <p class="mb-5 text-small text-paper-600">These appear in the footer, on the Contact page and on every product's WhatsApp button. Anything left blank is hidden from the website.</p>
        @elseif ($group === 'social')
            <p class="mb-5 text-small text-paper-600">Paste the full address of each page, starting with https://. Only networks with an address show an icon.</p>
        @endif

        <x-admin.card>
            <form method="POST" action="{{ route('admin.settings.update', $group) }}">
                @csrf @method('PUT')
                <fieldset class="grid gap-5" @cannot('settings.manage') disabled @endcannot>
                    @foreach ($settings as $s)
                        @php
                            $name = 'values['.$s->key.']';
                            $label = $s->label ?: \Illuminate\Support\Str::headline($s->key);
                            $inputType = match ($s->type) { 'email' => 'email', 'url' => 'url', 'number' => 'number', 'phone' => 'tel', default => 'text' };
                            $help = $s->key === 'whatsapp' ? 'Include the country code, e.g. +211 9XX XXX XXX. This turns on every WhatsApp button on the site.' : null;
                        @endphp
                        @if ($s->type === 'boolean')
                            <x-admin.toggle :name="$name" :label="$label" :checked="(bool) (int) $s->value" />
                        @elseif ($s->type === 'text')
                            <x-admin.textarea :name="$name" :label="$label" :value="$s->value" :rows="3" />
                        @else
                            <x-admin.input :name="$name" :label="$label" :value="$s->value" :type="$inputType" :step="$s->type === 'number' ? 'any' : null" :help="$help" />
                        @endif
                    @endforeach

                    @can('settings.manage')
                        <div><x-admin.button>Save {{ strtolower($groups[$group]) }}</x-admin.button></div>
                    @else
                        <p class="text-small text-paper-500">You can view these settings but not change them.</p>
                    @endcan
                </fieldset>
            </form>
        </x-admin.card>
    </div>
</x-admin.layout>
