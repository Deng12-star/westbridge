<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Product management over the API - the same rules as the admin panel. */
class ProductAdminController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAbility($request, 'products.create');

        $product = Product::query()->create($this->validated($request));

        return (new ProductResource($product->load('category')))->response()->setStatusCode(201);
    }

    public function update(Request $request, Product $product): ProductResource
    {
        $this->authorizeAbility($request, 'products.update');

        $product->update($this->validated($request, $product));

        return new ProductResource($product->fresh('category'));
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->authorizeAbility($request, 'products.delete');

        $product->delete();

        return response()->json(null, 204);
    }

    private function authorizeAbility(Request $request, string $permission): void
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        $allowed = $user && $user->can($permission)
            && (! $token || ! method_exists($token, 'can') || $token->can($permission) || $user->isSuperAdmin());

        abort_unless($allowed, 403, 'This token is not allowed to '.str_replace('.', ' ', $permission).'.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Product $product = null): array
    {
        $partial = $product !== null && $request->isMethod('patch');
        $req = $partial ? 'sometimes' : 'required';

        $data = $request->validate([
            'name' => [$req, 'string', 'max:190'],
            'slug' => ['sometimes', 'nullable', 'alpha_dash', 'max:190', Rule::unique('products', 'slug')->ignore($product?->id)],
            'product_category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('product_categories', 'id')],
            'sku' => ['sometimes', 'nullable', 'string', 'max:64'],
            'brand' => ['sometimes', 'nullable', 'string', 'max:120'],
            'short_description' => ['sometimes', 'nullable', 'string', 'max:300'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'stock_status' => ['sometimes', Rule::in(array_keys(Product::STOCK_STATUSES))],
            'specs' => ['sometimes', 'nullable', 'array', 'max:50'],
            'specs.*.label' => ['required_with:specs', 'string', 'max:100'],
            'specs.*.value' => ['nullable', 'string', 'max:300'],
            'is_published' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        if (! $product && blank($data['slug'] ?? null)) {
            $base = Str::slug($data['name']) ?: 'product';
            $slug = $base;
            $i = 2;
            while (Product::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        if (isset($data['currency'])) {
            $data['currency'] = Str::upper($data['currency']);
        }

        return $data;
    }
}
