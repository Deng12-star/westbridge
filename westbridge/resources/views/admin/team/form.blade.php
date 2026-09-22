@php($editing = $member->exists)

<x-admin.layout :title="$editing ? 'Edit team member' : 'Add team member'">
    <x-slot:actions>
        @if ($editing && $member->is_published)
            <x-admin.button variant="outline" :href="route('about').'#team'" target="_blank">View on site ↗</x-admin.button>
        @endif
        <x-admin.button variant="ghost" :href="route('admin.team.index')">← All team</x-admin.button>
    </x-slot:actions>

    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.team.update', $member) : route('admin.team.store') }}" class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid gap-6 xl:col-span-2">
            <x-admin.card title="Person">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-admin.input name="name" label="Full name" :value="$member->name" :required="true" />
                    <x-admin.input name="position" label="Position" :value="$member->position" :required="true" placeholder="e.g. Chief Executive Officer" />
                    <x-admin.textarea name="bio" label="Short bio" :value="$member->bio" :rows="4" maxlength="600" help="Optional. Two or three sentences, up to 600 characters." class="sm:col-span-2" />
                    <x-admin.input name="linkedin_url" type="url" label="LinkedIn profile" :value="$member->linkedin_url" placeholder="https://www.linkedin.com/in/..." class="sm:col-span-2" />
                </div>
            </x-admin.card>

            <x-admin.card title="Photo" description="A clear head-and-shoulders photo works best. It is shown in a circle.">
                <x-admin.image-field name="photo" label="Photo" :current="$member->photoUrl()" help="Square JPG, PNG or WebP, up to 5 MB. Without a photo, the person's initials are shown." />
            </x-admin.card>
        </div>

        <div class="grid content-start gap-6">
            <x-admin.card title="Visibility">
                <div class="grid gap-5">
                    <x-admin.toggle name="is_published" label="Show on the About page" :checked="$member->is_published" />
                    <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$member->sort_order ?? 0" help="Lower numbers appear first." />
                </div>
            </x-admin.card>
            <x-admin.button class="w-full py-3">{{ $editing ? 'Save' : 'Add team member' }}</x-admin.button>
        </div>
    </form>

    @if ($editing)
        @can('content.delete')
            <form method="POST" action="{{ route('admin.team.destroy', $member) }}" class="mt-10 border-t border-paper-300 pt-6" onsubmit="return confirm('Remove {{ addslashes($member->name) }} from the team?')">
                @csrf @method('DELETE')
                <x-admin.button variant="danger">Remove from team</x-admin.button>
            </form>
        @endcan
    @endif
</x-admin.layout>
