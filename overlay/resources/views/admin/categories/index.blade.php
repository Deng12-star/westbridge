<x-admin.layout title="Categories">
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="overflow-hidden rounded-sm border border-paper-300 bg-white xl:col-span-2">
            <table class="w-full text-left text-small">
                <thead class="border-b border-paper-200 bg-paper-50 font-mono text-[11px] uppercase tracking-wider text-paper-500">
                    <tr><th class="px-5 py-3">Category</th><th class="px-5 py-3">Products</th><th class="px-5 py-3">In menu</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-paper-200">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-5 py-3">
                                <span class="block font-display text-[15px] font-semibold text-navy-700">{{ $category->name }}</span>
                                <span class="font-mono text-xs text-paper-500">/shop/{{ $category->slug }}</span>
                            </td>
                            <td class="px-5 py-3 font-mono">{{ $category->products_count }}</td>
                            <td class="px-5 py-3">{{ $category->is_visible ? 'Yes' : 'Hidden' }}</td>
                            <td class="px-5 py-3 text-right">
                                @can('categories.update')
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="font-display text-sm font-semibold text-navy-700 hover:text-lime-700">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-paper-500">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @can('categories.create')
            <x-admin.card title="Add a category" description="Categories appear in the Shop menu and as filters on the shop page.">
                <form method="POST" action="{{ route('admin.categories.store') }}" class="grid gap-5">
                    @csrf
                    <x-admin.input name="name" label="Name" :required="true" />
                    <x-admin.input name="sort_order" type="number" min="0" label="Order" value="0" help="Lower numbers appear first." />
                    <x-admin.toggle name="is_visible" label="Show in the menu" :checked="true" />
                    <x-admin.button>Add category</x-admin.button>
                </form>
            </x-admin.card>
        @endcan
    </div>
</x-admin.layout>
