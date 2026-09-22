<x-admin.layout title="Messages">
    <div class="mb-5 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.messages.index') }}" @class(['rounded-full px-4 py-1.5 font-display text-sm font-semibold', 'bg-navy-700 text-white' => blank(request('filter')), 'bg-white text-navy-700 border border-paper-300' => filled(request('filter'))])>All</a>
        <a href="{{ route('admin.messages.index', ['filter' => 'unread']) }}" @class(['rounded-full px-4 py-1.5 font-display text-sm font-semibold', 'bg-navy-700 text-white' => request('filter') === 'unread', 'bg-white text-navy-700 border border-paper-300' => request('filter') !== 'unread'])>Unread ({{ $unread }})</a>
        @can('leads.delete')
            <a href="{{ route('admin.messages.index', ['filter' => 'deleted']) }}" @class(['rounded-full px-4 py-1.5 font-display text-sm font-semibold', 'bg-navy-700 text-white' => request('filter') === 'deleted', 'bg-white text-navy-700 border border-paper-300' => request('filter') !== 'deleted'])>Recently deleted</a>
        @endcan
        @if ($unread > 0)
            @can('leads.update')
                <form method="POST" action="{{ route('admin.messages.read-all') }}" class="ml-auto">
                    @csrf @method('PATCH')
                    <button class="rounded-full border border-paper-300 bg-white px-4 py-1.5 font-display text-sm font-semibold text-navy-700 hover:border-navy-400">Mark all as read</button>
                </form>
            @endcan
        @endif
    </div>

    <div class="overflow-hidden rounded-sm border border-paper-300 bg-white">
        @forelse ($messages as $message)
            @if ($message->trashed())
                <div class="flex items-center justify-between gap-4 border-b border-paper-200 px-5 py-4 last:border-0">
                    <span class="min-w-0">
                        <span class="block font-display text-[15px] font-medium text-navy-700">{{ $message->name }} <span class="font-sans text-xs font-normal text-paper-500">{{ $message->email }}</span></span>
                        <span class="block truncate text-small text-paper-600">{{ \Illuminate\Support\Str::limit($message->message, 120) }}</span>
                        <span class="text-xs text-paper-500">Deleted {{ $message->deleted_at->diffForHumans() }} · removed for good after {{ \App\Models\ContactMessage::RESTORE_DAYS }} days</span>
                    </span>
                    <form method="POST" action="{{ route('admin.messages.restore', $message) }}">
                        @csrf @method('PATCH')
                        <button class="font-display text-sm font-semibold text-lime-700 hover:text-navy-700">Restore</button>
                    </form>
                </div>
                @continue
            @endif
            <div class="group flex items-center gap-2 border-b border-paper-200 pr-4 last:border-0 hover:bg-paper-50">
            <a href="{{ route('admin.messages.show', $message) }}" class="flex min-w-0 flex-1 items-start gap-4 px-5 py-4">
                <span @class(['mt-2 h-2 w-2 flex-shrink-0 rounded-full', 'bg-lime-500' => ! $message->is_read, 'bg-transparent' => $message->is_read])></span>
                <span class="min-w-0 flex-1">
                    <span class="flex flex-wrap items-baseline justify-between gap-x-4">
                        <span @class(['font-display text-[15px] text-navy-700', 'font-bold' => ! $message->is_read, 'font-medium' => $message->is_read])>{{ $message->name }} <span class="font-sans text-xs font-normal text-paper-500">{{ $message->email }}</span></span>
                        <span class="text-xs text-paper-500">{{ $message->created_at->format('j M Y, H:i') }}</span>
                    </span>
                    <span class="mt-0.5 block truncate text-small text-paper-600">
                        @if ($message->subject)<span class="font-medium text-navy-700">{{ $message->subject }} — </span>@endif{{ \Illuminate\Support\Str::limit($message->message, 140) }}
                    </span>
                </span>
            </a>
            @can('leads.update')
                <form method="POST" action="{{ $message->is_read ? route('admin.messages.unread', $message) : route('admin.messages.read', $message) }}" class="flex-shrink-0">
                    @csrf @method('PATCH')
                    <input type="hidden" name="from" value="list">
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                    <button class="rounded-xs px-2.5 py-1.5 font-display text-xs font-semibold text-paper-500 transition-colors hover:bg-white hover:text-navy-700" title="{{ $message->is_read ? 'Mark unread' : 'Mark as read' }}">
                        {{ $message->is_read ? 'Mark unread' : 'Mark as read' }}
                    </button>
                </form>
            @endcan
            </div>
        @empty
            <p class="px-6 py-16 text-center text-paper-500">No messages{{ request('filter') === 'unread' ? ' waiting' : ' yet' }}.</p>
        @endforelse
    </div>

    <div class="mt-5">{{ $messages->links() }}</div>
</x-admin.layout>
