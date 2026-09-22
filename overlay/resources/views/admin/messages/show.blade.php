@php
    $replySubject = 'Re: '.($message->subject ?: 'Your enquiry to '.setting('company.name', 'WestBridge Technologies'));
    $digits = preg_replace('/\D+/', '', (string) $message->phone);
@endphp

<x-admin.layout title="Message">
    <x-slot:actions>
        <x-admin.button variant="ghost" :href="route('admin.messages.index')">← All messages</x-admin.button>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-3">
        <x-admin.card class="xl:col-span-2">
            <p class="text-xs text-paper-500">{{ $message->created_at->format('l j F Y, H:i') }}@if ($message->source_page) · from {{ $message->source_page }}@endif</p>
            <h2 class="mt-2 font-display text-xl font-bold text-navy-700">{{ $message->subject ?: 'No subject' }}</h2>
            <div class="mt-5 whitespace-pre-line text-[15px] leading-relaxed text-paper-800">{{ $message->message }}</div>
        </x-admin.card>

        <div class="grid content-start gap-6">
            <x-admin.card title="From">
                <p class="font-display text-[15px] font-semibold text-navy-700">{{ $message->name }}</p>
                <p class="mt-1 text-small"><a href="mailto:{{ $message->email }}" class="text-navy-700 underline hover:text-lime-700">{{ $message->email }}</a></p>
                @if ($message->phone)
                    <p class="mt-1 font-mono text-small text-paper-700">{{ $message->phone }}</p>
                @endif

                <div class="mt-5 grid gap-2">
                    <x-admin.button :href="'mailto:'.$message->email.'?subject='.rawurlencode($replySubject)">Reply by email</x-admin.button>
                    @if ($digits)
                        <x-admin.button variant="outline" :href="'https://wa.me/'.$digits" target="_blank" rel="noopener">Reply on WhatsApp</x-admin.button>
                    @endif
                </div>
            </x-admin.card>

            <div class="flex flex-wrap gap-3">
                @can('leads.update')
                    @if ($message->is_read)
                        <form method="POST" action="{{ route('admin.messages.unread', $message) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="from" value="list">
                            <x-admin.button variant="outline">Mark unread</x-admin.button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.messages.read', $message) }}">
                            @csrf @method('PATCH')
                            <x-admin.button variant="outline">Mark as read</x-admin.button>
                        </form>
                    @endif
                @endcan
                @can('leads.delete')
                    <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete this message? It can be restored from Recently deleted for 30 days.')">@csrf @method('DELETE')<x-admin.button variant="danger">Delete</x-admin.button></form>
                @endcan
            </div>
        </div>
    </div>
</x-admin.layout>
