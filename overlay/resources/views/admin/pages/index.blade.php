<x-admin.layout title="Pages">
    <p class="mb-5 max-w-3xl text-small text-paper-600">Edit the words on the website's pages. Layout and design stay as they are; only the text changes.</p>

    <div class="overflow-hidden rounded-sm border border-paper-300 bg-white">
        @foreach ($pages as $page)
            <a href="{{ route('admin.pages.edit', $page) }}" class="flex items-center justify-between gap-4 border-b border-paper-200 px-5 py-4 last:border-0 hover:bg-paper-50">
                <span>
                    <span class="block font-display text-[15px] font-semibold text-navy-700">{{ $page->title }}</span>
                    <span class="block text-small text-paper-600">{{ \Illuminate\Support\Str::limit($page->heading, 90) }}</span>
                </span>
                <span class="text-xs text-paper-500">Updated {{ $page->updated_at?->diffForHumans() }}</span>
            </a>
        @endforeach
    </div>
</x-admin.layout>
