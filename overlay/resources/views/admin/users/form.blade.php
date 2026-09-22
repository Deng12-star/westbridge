@php($editing = $user->exists)

<x-admin.layout :title="$editing ? 'Edit staff member' : 'Add staff member'">
    <x-slot:actions>
        <x-admin.button variant="ghost" :href="route('admin.users.index')">← All staff</x-admin.button>
    </x-slot:actions>

    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                @if ($editing) @method('PUT') @endif
                <x-admin.input name="name" label="Full name" :value="$user->name" :required="true" />
                <x-admin.input name="email" type="email" label="Email (used to sign in)" :value="$user->email" :required="true" />
                <x-admin.input name="phone" label="Phone" :value="$user->phone" />
                <x-admin.input name="job_title" label="Job title" :value="$user->job_title" />
                <x-admin.select name="role" label="Role" :options="array_combine($roles, $roles)" :value="$user->getRoleNames()->first() ?? 'Sales'" />
                <div class="flex items-end"><x-admin.toggle name="is_active" label="Account active" :checked="$user->is_active ?? true" help="Deactivated staff cannot sign in." /></div>
                <x-admin.input name="password" type="password" :label="$editing ? 'New password' : 'Password'" :required="! $editing" autocomplete="new-password" :help="$editing ? 'Leave blank to keep the current password.' : 'At least 10 characters.'" />
                <x-admin.input name="password_confirmation" type="password" label="Repeat password" :required="! $editing" autocomplete="new-password" />
                <div class="sm:col-span-2"><x-admin.button>{{ $editing ? 'Save' : 'Create account' }}</x-admin.button></div>
            </form>
        </x-admin.card>

        @if ($editing && ! $user->is(auth()->user()))
            @can('users.delete')
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-8" onsubmit="return confirm('Delete this account?')">
                    @csrf @method('DELETE')
                    <x-admin.button variant="danger">Delete account</x-admin.button>
                </form>
            @endcan
        @endif
    </div>
</x-admin.layout>
