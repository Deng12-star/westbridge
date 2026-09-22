<x-admin.layout title="Dashboard">
    <p class="font-display text-2xl font-bold text-navy-700">Hello, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}.</p>
    <p class="mt-1 text-paper-600">Here is what is happening on the website.</p>

    @if ($stuckEmails > 0 || $failedEmails > 0)
        <div class="mt-6 rounded-sm border border-status-warn/40 bg-status-warn/5 px-5 py-4 text-small text-paper-800" role="alert">
            <p class="font-display font-bold text-status-warn">Email is not being delivered</p>
            @if ($stuckEmails > 0)
                <p class="mt-1">{{ $stuckEmails }} {{ \Illuminate\Support\Str::plural('email', $stuckEmails) }} waiting more than 10 minutes. The background worker is not running - on the live server, check the <code class="font-mono">schedule:run</code> cron job.</p>
            @endif
            @if ($failedEmails > 0)
                <p class="mt-1">{{ $failedEmails }} failed in the last 7 days - usually wrong mail settings in <code class="font-mono">.env</code>. New messages are still saved under Messages.</p>
            @endif
        </div>
    @endif

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            @php($tag = $stat['url'] ? 'a' : 'div')
            <{{ $tag }} @if ($stat['url']) href="{{ $stat['url'] }}" @endif class="group rounded-sm border border-paper-300 bg-white p-6 transition-colors {{ $stat['url'] ? 'hover:border-navy-400' : '' }}">
                <p class="font-mono text-[11px] uppercase tracking-wider text-paper-500">{{ $stat['label'] }}</p>
                <p class="mt-3 font-display text-[2.25rem] font-bold leading-none text-navy-700">{{ number_format($stat['value']) }}</p>
            </{{ $tag }}>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <x-admin.card title="Recent messages" description="From the contact form." class="xl:col-span-2">
            @forelse ($recentMessages as $message)
                <a href="{{ route('admin.messages.show', $message) }}" class="-mx-3 flex items-start gap-4 rounded-xs px-3 py-3 hover:bg-paper-100">
                    <span @class(['mt-2 h-2 w-2 flex-shrink-0 rounded-full', 'bg-lime-500' => ! $message->is_read, 'bg-paper-300' => $message->is_read])></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-baseline justify-between gap-3">
                            <span @class(['truncate font-display text-[15px] text-navy-700', 'font-bold' => ! $message->is_read, 'font-medium' => $message->is_read])>{{ $message->name }}</span>
                            <span class="flex-shrink-0 text-xs text-paper-500">{{ $message->created_at->diffForHumans() }}</span>
                        </span>
                        <span class="block truncate text-small text-paper-600">{{ $message->subject ?: \Illuminate\Support\Str::limit($message->message, 90) }}</span>
                    </span>
                </a>
            @empty
                <p class="text-small text-paper-500">No messages yet. They appear here as soon as someone uses the contact form.</p>
            @endforelse
        </x-admin.card>

        <x-admin.card title="Finish setting up" description="Each of these makes the site look complete.">
            <ul class="grid gap-2.5">
                @foreach ($checklist as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="flex items-center gap-3 text-small {{ $item['done'] ? 'text-paper-500' : 'text-navy-700 hover:text-lime-700' }}">
                            <span @class([
                                'flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full',
                                'bg-lime-500 text-navy-900' => $item['done'],
                                'border border-paper-400' => ! $item['done'],
                            ])>
                                @if ($item['done'])<x-icons.wb name="check" class="h-3 w-3" />@endif
                            </span>
                            <span @class(['line-through' => $item['done'], 'font-medium' => ! $item['done']])>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    </div>

    <x-admin.card title="Most asked-about products" description="WhatsApp taps from product pages in the last 30 days." class="mt-6">
        @forelse ($topProducts as $row)
            <div class="flex items-center justify-between border-b border-paper-200 py-2.5 last:border-0">
                <span class="text-[15px] text-navy-700">{{ $row->product?->name ?? 'Deleted product' }}</span>
                <span class="font-mono text-sm text-paper-600">{{ $row->clicks }}</span>
            </div>
        @empty
            <p class="text-small text-paper-500">Nothing yet. Every time a visitor taps "Order on WhatsApp" it is counted here.</p>
        @endforelse
    </x-admin.card>
</x-admin.layout>
