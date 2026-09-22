<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Content\PortfolioRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Public, read-only endpoints. Everything here is already visible on the
 * website; nothing private is exposed.
 */
class CatalogueController extends Controller
{
    public function products(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'category' => ['nullable', 'string', 'max:120'],
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $products = Product::query()->published()->with('category')
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->string('q').'%')->orWhere('sku', 'like', '%'.$request->string('q').'%')->orWhere('brand', 'like', '%'.$request->string('q').'%')))
            ->ordered()
            ->paginate($request->integer('per_page', 24))
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function product(string $slug): ProductResource
    {
        return new ProductResource(Product::query()->published()->with('category')->where('slug', $slug)->firstOrFail());
    }

    public function categories(): JsonResponse
    {
        return response()->json([
            'data' => ProductCategory::query()->visible()->ordered()
                ->withCount(['products' => fn ($q) => $q->published()])
                ->get()
                ->map(fn (ProductCategory $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'description' => $c->description,
                    'products_count' => $c->products_count,
                    'url' => route('shop.category', $c->slug),
                ]),
        ]);
    }

    public function projects(PortfolioRepository $portfolio): JsonResponse
    {
        return response()->json(['data' => $portfolio->all()->map(fn (array $p) => $this->projectData($p))->values()]);
    }

    public function project(string $slug, PortfolioRepository $portfolio): JsonResponse
    {
        $project = $portfolio->find($slug);
        abort_if($project === null, 404);

        return response()->json(['data' => $this->projectData($project)]);
    }

    /** @param array<string, mixed> $project */
    private function projectData(array $project): array
    {
        return collect($project)->only([
            'title', 'slug', 'client', 'location', 'industry', 'category', 'service', 'status', 'year',
            'summary', 'overview', 'scope', 'image', 'live_url',
        ])->put('url', route('portfolio.show', $project['slug']))->all();
    }

    /** Public company details - the same ones printed in the site footer. */
    public function site(): JsonResponse
    {
        return response()->json(['data' => array_filter([
            'name' => setting('company.name', 'WestBridge Technologies'),
            'tagline' => setting('company.tagline'),
            'description' => setting('company.short_description'),
            'phone' => setting('contact.phone'),
            'whatsapp' => setting('contact.whatsapp'),
            'email' => setting('contact.email'),
            'address' => setting('contact.address'),
            'city' => setting('contact.city'),
            'country' => setting('contact.country'),
            'hours' => setting('contact.hours'),
            'socials' => active_socials() ?: null,
            'website' => url('/'),
        ], fn ($v) => filled($v))]);
    }
}
