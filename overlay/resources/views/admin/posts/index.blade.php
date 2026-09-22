<x-admin.layout title="News & updates">
    <x-slot:actions>
        @can('content.create')
            <x-admin.button :href="route('admin.posts.create')">+ Write a post</x-admin.button>
        @endcan
    </x-slot:actions>

    <div class="mb-5 flex flex-wrap gap-2">
        @foreach (['' => 'All', 'published' => 'Published', 'scheduled' => 'Scheduled', 'draft' => 'Drafts'] as $value => $label)
            <a href="{{ route('admin.posts.index', array_filter(['status' => $value])) }}" @class([
                'rounded-full px-4 py-1.5 font-display text-sm font-semibold',
                'bg-navy-700 text-white' => (string) request('status') === $value,
                'border border-paper-300 bg-white text-navy-700' => (string) request('status') !== $value,
            ])>{{ $label }}</a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-sm border border-paper-300 bg-white">
        @forelse ($posts as $post)
            <a href="{{ route('admin.posts.edit', $post) }}" class="flex items-center gap-4 border-b border-paper-200 px-5 py-4 last:border-0 hover:bg-paper-50">
                <span class="hidden h-14 w-20 flex-shrink-0 overflow-hidden rounded-xs bg-paper-100 sm:block">
                    @if ($post->coverUrl())
                        <img src="{{ $post->coverUrl() }}" alt="" class="h-full w-full object-cover" loading="lazy">
                    @endif
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-display text-[15px] font-semibold text-navy-700">{{ $post->title }}</span>
                    <span class="block text-xs text-paper-500">{{ $post->category }} · {{ $post->author?->name ?? 'WestBridge' }}
                        @if ($post->published_at) · {{ $post->published_at->format('j M Y, H:i') }} @endif
                    </span>
                </span>
                <span @class([
                    'flex-shrink-0 rounded-full px-2.5 py-1 font-mono text-[11px] font-semibold uppercase tracking-wider',
                    'bg-lime-100 text-lime-800' => $post->statusLabel() === 'Published',
                    'bg-navy-50 text-navy-700' => $post->statusLabel() === 'Scheduled',
                    'bg-paper-200 text-paper-600' => $post->statusLabel() === 'Draft',
                ])>{{ $post->statusLabel() }}</span>
            </a>
        @empty
            <div class="px-6 py-16 text-center">
                <p class="font-display text-lg font-bold text-navy-700">No posts{{ request('status') ? ' here' : ' yet' }}</p>
                <p class="mt-1 text-small text-paper-600">Share news, completed installations, new products or announcements. The News link appears on the website with the first published post.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $posts->links() }}</div>
</x-admin.layout>
