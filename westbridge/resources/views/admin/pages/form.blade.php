@php
    $labels = [
        'who_we_are' => 'Who we are',
        'what_we_do' => 'What we do',
        'mission' => 'Mission',
        'vision' => 'Vision',
        'story' => 'Our story',
        'why_technology' => 'Why technology',
        'values' => 'Values',
        'approach' => 'How we work',
        'body' => 'Page text',
    ];
@endphp

<x-admin.layout :title="'Edit page: '.$page->title">
    <x-slot:actions>
        @if ($publicUrl)
            <x-admin.button variant="outline" :href="$publicUrl" target="_blank">View page ↗</x-admin.button>
        @endif
        <x-admin.button variant="ghost" :href="route('admin.pages.index')">← All pages</x-admin.button>
    </x-slot:actions>

    <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="grid gap-6 xl:grid-cols-3">
        @csrf @method('PUT')

        <div class="grid gap-6 xl:col-span-2">
            <x-admin.card title="Top of the page">
                <div class="grid gap-5">
                    <x-admin.input name="heading" label="Heading" :value="$page->heading" />
                    <x-admin.textarea name="lede" label="Introduction" :value="$page->lede" :rows="3" />
                </div>
            </x-admin.card>

            @if (filled($blocks))
                <x-admin.card title="Sections" description="A section left empty is hidden on the website.">
                    <div class="grid gap-7">
                        @foreach ($blocks as $key => $value)
                            @if (in_array($key, $listBlocks, true) || is_array($value))
                                @php $rows = array_merge(array_values((array) $value), [['title' => '', 'body' => ''], ['title' => '', 'body' => '']]); @endphp
                                <fieldset>
                                    <legend class="font-display text-sm font-semibold text-navy-700">{{ $labels[$key] ?? \Illuminate\Support\Str::headline($key) }}</legend>
                                    <p class="text-xs text-paper-500">Each item has a title and a short description. Clear the title to remove an item; two empty rows are always there for new ones.</p>
                                    <div class="mt-3 grid gap-3">
                                        @foreach ($rows as $i => $row)
                                            <div class="grid gap-2 rounded-xs border border-paper-200 bg-paper-50 p-3 sm:grid-cols-[14rem_1fr]">
                                                <input name="blocks[{{ $key }}][{{ $i }}][title]" value="{{ data_get($row, 'title') }}" placeholder="Title" class="rounded-xs border border-paper-300 bg-white px-3 py-2 text-[15px] font-semibold text-navy-700">
                                                <textarea name="blocks[{{ $key }}][{{ $i }}][body]" rows="2" placeholder="Description" class="rounded-xs border border-paper-300 bg-white px-3 py-2 text-[15px]">{{ data_get($row, 'body') }}</textarea>
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @else
                                <x-admin.textarea :name="'blocks['.$key.']'" :label="$labels[$key] ?? \Illuminate\Support\Str::headline($key)" :value="$value" :rows="in_array($key, ['mission', 'vision'], true) ? 3 : 8"
                                    help="Plain text is fine. Separate paragraphs with a blank line." />
                            @endif
                        @endforeach
                    </div>
                </x-admin.card>
            @endif
        </div>

        <div class="grid content-start gap-6">
            <x-admin.card title="Search engines" description="How this page appears on Google. Leave blank to use the defaults.">
                <div class="grid gap-5">
                    <x-admin.input name="meta_title" label="Page title" :value="$page->meta_title" maxlength="255" />
                    <x-admin.textarea name="meta_description" label="Description" :value="$page->meta_description" :rows="4" maxlength="500" help="About 150 characters is ideal." />
                </div>
            </x-admin.card>
            <x-admin.button class="w-full py-3">Save page</x-admin.button>
        </div>
    </form>
</x-admin.layout>
