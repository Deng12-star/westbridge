<x-admin.layout title="Projects">
    <x-slot:actions>
        @can('projects.create')
            <x-admin.button :href="route('admin.projects.create')">+ Add project</x-admin.button>
        @endcan
    </x-slot:actions>

    <p class="mb-5 max-w-3xl text-small text-paper-600">These are the case studies on the Portfolio page. The homepage shows an "Our Projects" section once at least three are visible.</p>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($projects as $project)
            <a href="{{ route('admin.projects.edit', $project) }}" class="group flex flex-col rounded-sm border border-paper-300 bg-white p-5 transition-colors hover:border-navy-400">
                <div class="flex items-start justify-between gap-3">
                    <span class="font-mono text-[11px] uppercase tracking-wider text-lime-700">{{ $project->category ?: 'Project' }}</span>
                    <span @class(['rounded-full px-2 py-0.5 font-mono text-[10px] uppercase', 'bg-lime-100 text-lime-800' => $project->is_published, 'bg-paper-200 text-paper-600' => ! $project->is_published])>{{ $project->is_published ? 'Visible' : 'Hidden' }}</span>
                </div>
                <h2 class="mt-2 font-display text-lg font-bold text-navy-700 group-hover:text-lime-700">{{ $project->title }}</h2>
                <p class="mt-1 line-clamp-2 text-small text-paper-600">{{ $project->summary }}</p>
                <p class="mt-auto pt-4 text-xs text-paper-500">{{ $project->client ?: 'Client not set' }}</p>
            </a>
        @empty
            <p class="text-paper-500">No projects yet.</p>
        @endforelse
    </div>
</x-admin.layout>
