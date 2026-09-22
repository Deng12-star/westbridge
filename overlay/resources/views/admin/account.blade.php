<x-admin.layout title="My account">
    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.account.update') }}" class="grid gap-5">
                @csrf @method('PUT')
                <x-admin.input name="name" label="Name" :value="$user->name" :required="true" />
                <x-admin.input name="phone" label="Phone" :value="$user->phone" />
                <p class="text-small text-paper-600">Signed in as <strong class="text-navy-700">{{ $user->email }}</strong>.</p>

                <div class="border-t border-paper-200 pt-5">
                    <p class="font-display text-sm font-semibold text-navy-700">Change password</p>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <x-admin.input name="current_password" type="password" label="Current" autocomplete="current-password" />
                        <x-admin.input name="password" type="password" label="New" autocomplete="new-password" />
                        <x-admin.input name="password_confirmation" type="password" label="Repeat new" autocomplete="new-password" />
                    </div>
                    <p class="mt-2 text-xs text-paper-500">At least 10 characters. Leave all three blank to keep your password.</p>
                </div>
                <div><x-admin.button>Save</x-admin.button></div>
            </form>
        </x-admin.card>
    </div>
</x-admin.layout>
