@php($editing = $project->exists)

<x-admin.layout :title="$editing ? 'Edit project' : 'Add project'">
    <x-slot:actions>
        @if ($editing && $project->is_published)
            <x-admin.button variant="outline" :href="route('portfolio.show', $project->slug)" target="_blank">View on site ↗</x-admin.button>
        @endif
        <x-admin.button variant="ghost" :href="route('admin.projects.index')">← All projects</x-admin.button>
    </x-slot:actions>

    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.projects.update', $project) : route('admin.projects.store') }}" class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid gap-6 xl:col-span-2">
            <x-admin.card title="The project">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-admin.input name="title" label="Title" :value="$project->title" :required="true" class="sm:col-span-2" />
                    <x-admin.textarea name="summary" label="Summary" :value="$project->summary" :rows="2" maxlength="400" help="One sentence - shown on the project card and at the top of the page." class="sm:col-span-2" />
                    <x-admin.textarea name="overview" label="Overview" :value="implode(PHP_EOL.PHP_EOL, (array) $project->overview)" :rows="7" help="Separate paragraphs with a blank line." class="sm:col-span-2" />
                    <x-admin.textarea name="scope" label="What the system does" :value="implode(PHP_EOL, (array) $project->scope)" :rows="6" help="One item per line." class="sm:col-span-2" />
                </div>
            </x-admin.card>

            <x-admin.card title="Screenshot" description="Without one, a drawn panel in the brand colours is shown instead.">
                <x-admin.image-field :current="$project->imageUrl()" help="A 1600 × 1000 screenshot of the system works best. Up to 5 MB." />
                <div class="mt-5 max-w-xs">
                    <x-admin.select name="mock" label="Drawn panel style" :options="\App\Models\Project::MOCKS" :value="$project->mock" help="Used only when there is no screenshot." />
                </div>
            </x-admin.card>
        </div>

        <div class="grid content-start gap-6">
            <x-admin.card title="Details">
                <div class="grid gap-5">
                    <x-admin.input name="client" label="Client" :value="$project->client" />
                    <x-admin.input name="location" label="Location" :value="$project->location" />
                    <x-admin.input name="industry" label="Sector" :value="$project->industry" />
                    <x-admin.input name="category" label="Category" :value="$project->category" help="For example Payroll & HR" />
                    <x-admin.input name="service" label="Service" :value="$project->service" />
                    <div class="grid grid-cols-2 gap-3">
                        <x-admin.input name="status" label="Status" :value="$project->status" placeholder="Live" />
                        <x-admin.input name="year" label="Year" :value="$project->year" />
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Link & visibility">
                <div class="grid gap-5">
                    <x-admin.input name="live_url" type="url" label="Live address" :value="$project->live_url" placeholder="https://" />
                    <x-admin.toggle name="show_live_link" label="Show a 'Visit the live system' button" :checked="$project->show_live_link" />
                    <x-admin.toggle name="is_published" label="Show on the website" :checked="$project->is_published" />
                    <x-admin.input name="slug" label="Web address" :value="$project->slug" help="Leave blank to make one from the title." />
                    <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$project->sort_order ?? 0" />
                </div>
            </x-admin.card>

            <x-admin.button class="w-full py-3">{{ $editing ? 'Save changes' : 'Add project' }}</x-admin.button>
        </div>
    </form>

    @if ($editing)
        @can('projects.delete')
            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" class="mt-10 border-t border-paper-300 pt-6" onsubmit="return confirm('Delete this project? This cannot be undone.')">
                @csrf @method('DELETE')
                <x-admin.button variant="danger">Delete this project</x-admin.button>
            </form>
        @endcan
    @endif
</x-admin.layout>
