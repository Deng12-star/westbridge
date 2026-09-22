<x-admin.layout title="Edit category">
    <x-slot:actions>
        <x-admin.button variant="ghost" :href="route('admin.categories.index')">← All categories</x-admin.button>
    </x-slot:actions>

    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="grid gap-5">
                @csrf @method('PUT')
                <x-admin.input name="name" label="Name" :value="$category->name" :required="true" />
                <x-admin.input name="slug" label="Web address" :value="$category->slug" help="Used in /shop/…  Changing it breaks links people may have saved." />
                <x-admin.textarea name="description" label="Description" :value="$category->description" :rows="3" help="Optional. Shown at the top of this category's page." />
                <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$category->sort_order" />
                <x-admin.toggle name="is_visible" label="Show in the menu" :checked="$category->is_visible" />
                <div><x-admin.button>Save</x-admin.button></div>
            </form>
        </x-admin.card>

        @can('categories.delete')
            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="mt-8" onsubmit="return confirm('Delete this category? Its products stay, uncategorised.')">
                @csrf @method('DELETE')
                <x-admin.button variant="danger">Delete category</x-admin.button>
            </form>
        @endcan
    </div>
</x-admin.layout>
