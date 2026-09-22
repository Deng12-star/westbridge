<x-admin.layout title="Team">
    <x-slot:actions>
        @can('content.create')
            <x-admin.button :href="route('admin.team.create')">+ Add team member</x-admin.button>
        @endcan
    </x-slot:actions>

    <p class="mb-5 max-w-3xl text-small text-paper-600">These people appear in the "Our team" section of the About page, in the order shown here. The section is hidden until at least one person is visible.</p>

    @if ($members->isEmpty())
        <div class="rounded-sm border border-paper-300 bg-white px-6 py-16 text-center">
            <p class="font-display text-lg font-bold text-navy-700">No team members yet</p>
            <p class="mt-1 text-small text-paper-600">Add each person with their name, position and a photo.</p>
            @can('content.create')
                <x-admin.button :href="route('admin.team.create')" class="mt-5">+ Add the first person</x-admin.button>
            @endcan
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($members as $member)
                <a href="{{ route('admin.team.edit', $member) }}" class="group flex flex-col items-center rounded-sm border border-paper-300 bg-white p-6 text-center transition-colors hover:border-navy-400">
                    <span class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-navy-700 font-display text-2xl font-bold text-lime-400">
                        @if ($member->photoUrl())
                            <img src="{{ $member->photoUrl() }}" alt="" class="h-full w-full object-cover">
                        @else
                            {{ $member->initials() }}
                        @endif
                    </span>
                    <span class="mt-4 font-display text-[15px] font-bold text-navy-700 group-hover:text-lime-700">{{ $member->name }}</span>
                    <span class="text-small text-paper-600">{{ $member->position }}</span>
                    <span class="mt-3 flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-paper-500">
                        #{{ $member->sort_order }}
                        <span @class(['rounded-full px-2 py-0.5', 'bg-lime-100 text-lime-800' => $member->is_published, 'bg-paper-200 text-paper-600' => ! $member->is_published])>{{ $member->is_published ? 'Visible' : 'Hidden' }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</x-admin.layout>
