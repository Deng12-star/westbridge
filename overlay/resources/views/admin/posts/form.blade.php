@php
    $editing = $post->exists;
    $canPublish = auth()->user()->can('content.publish');
    $when = old('published_at', $post->published_at?->format('Y-m-d\TH:i'));
@endphp

<x-admin.layout :title="$editing ? 'Edit post' : 'Write a post'">
    <x-slot:actions>
        @if ($editing && $post->isLive())
            <x-admin.button variant="outline" :href="route('news.show', $post->slug)" target="_blank">View on site ↗</x-admin.button>
        @endif
        <x-admin.button variant="ghost" :href="route('admin.posts.index')">← All posts</x-admin.button>
    </x-slot:actions>

    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.posts.update', $post) : route('admin.posts.store') }}" class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid gap-6 xl:col-span-2">
            <x-admin.card>
                <div class="grid gap-5">
                    <x-admin.input name="title" label="Title" :value="$post->title" :required="true" class="[&_input]:text-lg [&_input]:font-semibold" />
                    <x-admin.textarea name="excerpt" label="Summary" :value="$post->excerpt" :rows="2" maxlength="300" help="One or two sentences for the news list and Google. Leave blank to use the start of the post." />

                    <div x-data="{ help: false }">
                        <div class="flex items-center justify-between">
                            <label for="f-body" class="font-display text-sm font-semibold text-navy-700">Post <span class="text-status-crit">*</span></label>
                            <button type="button" @click="help = !help" class="text-xs font-semibold text-lime-700 hover:text-navy-700">Formatting help</button>
                        </div>
                        <div x-show="help" x-cloak x-transition class="mt-2 rounded-xs border border-paper-200 bg-paper-50 p-4 font-mono text-xs leading-relaxed text-paper-700">
                            A blank line &nbsp;&rarr; new paragraph<br>
                            ## Heading &nbsp;&rarr; a section heading<br>
                            - item &nbsp;&rarr; bullet list (one per line)<br>
                            1. item &nbsp;&rarr; numbered list<br>
                            &gt; text &nbsp;&rarr; quotation<br>
                            **bold** &nbsp;&rarr; <strong>bold</strong><br>
                            https://... &nbsp;&rarr; a link
                        </div>
                        <textarea id="f-body" name="body" rows="18" required
                            class="mt-1.5 block w-full rounded-xs border border-paper-300 bg-white px-4 py-3 text-[15px] leading-relaxed focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20"
                            placeholder="Write the post here. Leave a blank line between paragraphs.">{{ old('body', $post->body) }}</textarea>
                        @error('body')<p class="mt-1 text-xs text-status-crit">{{ $message }}</p>@enderror
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Cover image" description="Shown at the top of the post and on the news list.">
                <x-admin.image-field name="cover" label="Cover image" :current="$post->coverUrl()" help="A wide photo (about 1600 × 900) works best. JPG, PNG or WebP, up to 5 MB." />
            </x-admin.card>
        </div>

        <div class="grid content-start gap-6">
            <x-admin.card title="Publishing">
                <div class="grid gap-5">
                    @if ($canPublish)
                        <x-admin.toggle name="is_published" label="Publish" :checked="$post->is_published" help="Off = draft, only visible here." />
                    @else
                        <p class="rounded-xs bg-paper-100 px-3 py-2 text-small text-paper-700">Your role can write drafts. A Content Manager or Super Admin publishes them.</p>
                    @endif
                    <x-admin.input name="published_at" type="datetime-local" label="Publish date" :value="$when" help="Blank = now. A future date schedules the post." />
                    <x-admin.select name="category" label="Category" :options="array_combine(\App\Models\Post::CATEGORIES, \App\Models\Post::CATEGORIES)" :value="$post->category" />
                    <x-admin.input name="slug" label="Web address" :value="$post->slug" help="Leave blank to make one from the title." />
                    @if ($editing)
                        <p class="text-xs text-paper-500">Status: <strong class="text-navy-700">{{ $post->statusLabel() }}</strong></p>
                    @endif
                </div>
            </x-admin.card>
            <x-admin.button class="w-full py-3">{{ $editing ? 'Save' : 'Save post' }}</x-admin.button>
        </div>
    </form>

    @if ($editing)
        @can('content.delete')
            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="mt-10 border-t border-paper-300 pt-6" onsubmit="return confirm('Delete this post? This cannot be undone.')">
                @csrf @method('DELETE')
                <x-admin.button variant="danger">Delete post</x-admin.button>
            </form>
        @endcan
    @endif
</x-admin.layout>
