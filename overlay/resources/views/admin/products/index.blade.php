<x-admin.layout title="Products">
    <x-slot:actions>
        @can('products.create')
            <x-admin.button :href="route('admin.products.create')">+ Add product</x-admin.button>
        @endcan
    </x-slot:actions>

    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, SKU or brand"
            class="w-full max-w-xs rounded-xs border border-paper-300 bg-white px-3 py-2.5 text-[15px] focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20">
        <select name="category" class="rounded-xs border border-paper-300 bg-white px-3 py-2.5 text-[15px]">
            <option value="">All categories</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected((string) request('category') === (string) $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xs border border-paper-300 bg-white px-3 py-2.5 text-[15px]">
            <option value="">Visible and hidden</option>
            <option value="live" @selected(request('status') === 'live')>Visible only</option>
            <option value="hidden" @selected(request('status') === 'hidden')>Hidden only</option>
            @can('products.delete')
                <option value="deleted" @selected(request('status') === 'deleted')>Recently deleted</option>
            @endcan
        </select>
        <x-admin.button variant="outline">Filter</x-admin.button>
    </form>

    <div class="overflow-hidden rounded-sm border border-paper-300 bg-white">
        @if ($products->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="font-display text-lg font-bold text-navy-700">No products yet</p>
                <p class="mt-1 text-small text-paper-600">Add the items you stock. The shop page shows itself as soon as there is one.</p>
                @can('products.create')
                    <x-admin.button :href="route('admin.products.create')" class="mt-5">+ Add your first product</x-admin.button>
                @endcan
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-small">
                    <thead class="border-b border-paper-200 bg-paper-50 font-mono text-[11px] uppercase tracking-wider text-paper-500">
                        <tr>
                            <th class="px-5 py-3">Product</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3">Price</th>
                            <th class="px-5 py-3">Stock</th>
                            <th class="px-5 py-3">On site</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-paper-200">
                        @foreach ($products as $product)
                            <tr class="hover:bg-paper-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-xs border border-paper-200 bg-paper-50">
                                            @if ($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-contain" loading="lazy">
                                            @else
                                                <x-icons.wb name="device" class="h-5 w-5 text-paper-400" />
                                            @endif
                                        </span>
                                        <span class="min-w-0">
                                            <a href="{{ $product->trashed() ? '#' : route('admin.products.edit', $product) }}" class="block truncate font-display text-[15px] font-semibold text-navy-700 hover:text-lime-700">{{ $product->name }}</a>
                                            <span class="block font-mono text-xs text-paper-500">{{ $product->sku ?: '—' }} @if ($product->is_featured)<span class="ml-1 rounded-xs bg-lime-100 px-1 text-lime-800">Featured</span>@endif</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-paper-700">{{ $product->category?->name ?? 'Uncategorised' }}</td>
                                <td class="px-5 py-3 font-mono text-paper-700">{{ $product->formattedPrice() ?? 'On request' }}</td>
                                <td class="px-5 py-3 text-paper-700">{{ $product->stockLabel() }}</td>
                                <td class="px-5 py-3">
                                    @if ($product->trashed())
                                        <span class="text-xs text-paper-500">Deleted {{ $product->deleted_at->diffForHumans() }}</span>
                                    @elseif (auth()->user()->can('products.update'))
                                        <form method="POST" action="{{ route('admin.products.toggle', $product) }}">
                                            @csrf @method('PATCH')
                                            <button @class([
                                                'rounded-full px-2.5 py-1 font-mono text-[11px] font-semibold uppercase tracking-wider',
                                                'bg-lime-100 text-lime-800' => $product->is_published,
                                                'bg-paper-200 text-paper-600' => ! $product->is_published,
                                            ]) title="Click to {{ $product->is_published ? 'hide' : 'show' }}">{{ $product->is_published ? 'Visible' : 'Hidden' }}</button>
                                        </form>
                                    @else
                                        {{ $product->is_published ? 'Visible' : 'Hidden' }}
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if ($product->trashed())
                                        @can('products.delete')
                                            <form method="POST" action="{{ route('admin.products.restore', $product) }}">
                                                @csrf @method('PATCH')
                                                <button class="font-display text-sm font-semibold text-lime-700 hover:text-navy-700">Restore</button>
                                            </form>
                                        @endcan
                                    @else
                                        <a href="{{ route('admin.products.edit', $product) }}" class="font-display text-sm font-semibold text-navy-700 hover:text-lime-700">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-5">{{ $products->links() }}</div>
</x-admin.layout>
