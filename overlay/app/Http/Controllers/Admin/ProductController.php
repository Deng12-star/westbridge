<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Media\ImageUploader;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(private readonly ImageUploader $images) {}

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category:id,name')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $q->where('name', 'like', $term)->orWhere('sku', 'like', $term)->orWhere('brand', 'like', $term);
            }))
            ->when($request->filled('category'), fn ($q) => $q->where('product_category_id', $request->integer('category')))
            ->when($request->input('status') === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when($request->input('status') === 'hidden', fn ($q) => $q->where('is_published', false))
            ->when($request->input('status') === 'live', fn ($q) => $q->where('is_published', true))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => ProductCategory::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['is_published' => true, 'stock_status' => 'in_stock', 'currency' => setting('shop.currency', 'USD')]),
            'categories' => ProductCategory::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->images->store($request->file('image'), 'products');
        }

        $product = Product::query()->create($data);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product added.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => ProductCategory::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $product->id);

        if ($request->hasFile('image')) {
            $this->images->delete($product->image_path);
            $data['image_path'] = $this->images->store($request->file('image'), 'products');
        } elseif ($request->boolean('remove_image')) {
            $this->images->delete($product->image_path);
            $data['image_path'] = null;
        }

        $product->update($data);

        return back()->with('status', 'Product saved.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_published' => ! $product->is_published]);

        return back()->with('status', $product->is_published ? 'Product is now visible in the shop.' : 'Product hidden from the shop.');
    }

    /** Moves the product to Recently deleted; its photo is kept until it is removed for good. */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('status', 'Product deleted. You can restore it from "Recently deleted" for '.Product::RESTORE_DAYS.' days.');
    }

    public function restore(Product $product): RedirectResponse
    {
        $product->restore();

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product restored.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash'],
            'product_category_id' => ['nullable', 'integer', Rule::exists('product_categories', 'id')],
            'sku' => ['nullable', 'string', 'max:64'],
            'brand' => ['nullable', 'string', 'max:120'],
            'short_description' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'currency' => ['required', 'string', 'size:3'],
            'stock_status' => ['required', Rule::in(array_keys(Product::STOCK_STATUSES))],
            'specs' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        unset($data['image']);

        return [
            ...$data,
            'slug' => $data['slug'] ?? null,
            'currency' => Str::upper($data['currency']),
            'specs' => $this->parseSpecs($data['specs'] ?? ''),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
        ];
    }

    /**
     * Specifications are typed one per line as "Label: value" - the simplest
     * thing a non-technical editor can get right.
     *
     * @return list<array{label: string, value: string}>
     */
    private function parseSpecs(string $text): array
    {
        return collect(preg_split('/\R/', $text) ?: [])
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->map(function (string $line): array {
                [$label, $value] = array_pad(explode(':', $line, 2), 2, '');

                return ['label' => trim($label), 'value' => trim($value)];
            })
            ->values()
            ->all();
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'product';
        $slug = $base;
        $i = 2;

        // withTrashed: a deleted (restorable) product still owns its address.
        while (Product::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
