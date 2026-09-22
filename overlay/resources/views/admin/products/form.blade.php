@php
    $editing = $product->exists;
    $specText = collect($product->specs ?? [])
        ->map(fn ($s) => trim(($s['label'] ?? '').': '.($s['value'] ?? ''), ': '))
        ->join("\n");
@endphp

<x-admin.layout :title="$editing ? 'Edit product' : 'Add product'">
    <x-slot:actions>
        @if ($editing && $product->is_published)
            <x-admin.button variant="outline" :href="route('shop.product', $product->slug)" target="_blank">View in shop ↗</x-admin.button>
        @endif
        <x-admin.button variant="ghost" :href="route('admin.products.index')">← All products</x-admin.button>
    </x-slot:actions>

    <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="grid gap-6 xl:col-span-2">
            <x-admin.card title="Product">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-admin.input name="name" label="Name" :value="$product->name" :required="true" class="sm:col-span-2" />
                    <x-admin.select name="product_category_id" label="Category" :options="$categories->pluck('name', 'id')" :value="$product->product_category_id" placeholder="Uncategorised" />
                    <x-admin.input name="brand" label="Brand" :value="$product->brand" />
                    <x-admin.input name="sku" label="SKU / model number" :value="$product->sku" help="Optional. Shown on the product page and in the WhatsApp message." />
                    <x-admin.input name="slug" label="Web address" :value="$product->slug" help="Leave blank to make one from the name." />
                    <x-admin.textarea name="short_description" label="Short description" :value="$product->short_description" :rows="2" help="One or two lines shown on the product card. Up to 300 characters." class="sm:col-span-2" maxlength="300" />
                    <x-admin.textarea name="description" label="Full description" :value="$product->description" :rows="7" help="Shown on the product page. Separate paragraphs with a blank line." class="sm:col-span-2" />
                    <x-admin.textarea name="specs" label="Specifications" :value="$specText" :rows="6" help="One per line, as Label: value - for example  Memory: 16 GB" class="sm:col-span-2" placeholder="Processor: Intel Core i5&#10;Memory: 16 GB&#10;Storage: 512 GB SSD" />
                </div>
            </x-admin.card>

            <x-admin.card title="Photo">
                <x-admin.image-field :current="$product->image_url" />
            </x-admin.card>
        </div>

        <div class="grid content-start gap-6">
            <x-admin.card title="Price & availability">
                <div class="grid gap-5">
                    <div class="grid grid-cols-[1fr_6rem] gap-3">
                        <x-admin.input name="price" type="number" step="0.01" min="0" label="Price" :value="$product->price" />
                        <x-admin.input name="currency" label="Currency" :value="$product->currency ?: 'USD'" maxlength="3" />
                    </div>
                    <p class="-mt-3 text-xs text-paper-500">Leave the price blank to show "Price on request".</p>
                    <x-admin.select name="stock_status" label="Availability" :options="\App\Models\Product::STOCK_STATUSES" :value="$product->stock_status" />
                </div>
            </x-admin.card>

            <x-admin.card title="Visibility">
                <div class="grid gap-5">
                    <x-admin.toggle name="is_published" label="Show in the shop" :checked="$product->is_published" />
                    <x-admin.toggle name="is_featured" label="Featured" help="Featured items are listed first." :checked="$product->is_featured" />
                    <x-admin.input name="sort_order" type="number" min="0" label="Order" :value="$product->sort_order ?? 0" help="Lower numbers appear first." />
                </div>
            </x-admin.card>

            <x-admin.button class="w-full py-3">{{ $editing ? 'Save changes' : 'Add product' }}</x-admin.button>
        </div>
    </form>

    @if ($editing)
        @can('products.delete')
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="mt-10 border-t border-paper-300 pt-6" onsubmit="return confirm('Delete {{ addslashes($product->name) }}? It can be restored from Recently deleted for 30 days.')">
                @csrf @method('DELETE')
                <x-admin.button variant="danger">Delete this product</x-admin.button>
            </form>
        @endcan
    @endif
</x-admin.layout>
