<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => ProductCategory::query()->withCount('products')->ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        ProductCategory::query()->create($data);

        return back()->with('status', 'Category added.');
    }

    public function edit(ProductCategory $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('status', 'Category saved.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        // Products are kept - they simply become uncategorised.
        $category->delete();

        return back()->with('status', 'Category deleted. Its products are now uncategorised.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?ProductCategory $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('product_categories', 'slug')->ignore($category?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        $slug = Str::slug($data['slug'] ?? '' ?: $data['name']);

        if (ProductCategory::query()->where('slug', $slug)->when($category, fn ($q) => $q->whereKeyNot($category->id))->exists()) {
            $slug .= '-'.Str::lower(Str::random(4));
        }

        return [
            ...$data,
            'slug' => $slug,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_visible' => $request->boolean('is_visible', true),
        ];
    }
}
