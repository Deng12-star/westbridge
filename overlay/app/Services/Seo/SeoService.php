<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Content\PortfolioRepository;
use Illuminate\Support\Facades\Schema;

/**
 * Structured data and the sitemap, in one place.
 *
 * Schema keys that begin with "@" are built from a variable ($this->at)
 * rather than written literally: Blade scans templates for directives, and a
 * literal at-context inside a view is compiled as Laravel's context
 * directive. Keeping schema out of Blade removes that trap entirely.
 */
class SeoService
{
    private string $at = '@';

    public function __construct(private readonly PortfolioRepository $portfolio) {}

    /** @return array<string, mixed> */
    public function organizationSchema(): array
    {
        return $this->clean([
            $this->at.'context' => 'https://schema.org',
            $this->at.'type' => 'Organization',
            'name' => setting('company.name', 'WestBridge Technologies'),
            'slogan' => setting('company.tagline', 'Connecting Ideas. Building Tomorrow.'),
            'url' => url('/'),
            'logo' => asset('brand/icon-512.png'),
            'description' => setting('company.short_description'),
            'address' => [
                $this->at.'type' => 'PostalAddress',
                'streetAddress' => setting('contact.address'),
                'addressLocality' => setting('contact.city', 'Juba'),
                'addressCountry' => 'SS',
            ],
            'telephone' => setting('contact.phone'),
            'email' => setting('contact.email'),
            'sameAs' => array_values(active_socials()),
        ]);
    }

    /**
     * Product markup. An Offer is included only when a real price is shown:
     * Google treats a missing or zero price as an error, so a "price on
     * request" item is described without one.
     *
     * @return array<string, mixed>
     */
    public function productSchema(Product $product): array
    {
        $showPrices = (bool) (int) setting('shop.show_prices', '1');

        return $this->clean([
            $this->at.'context' => 'https://schema.org',
            $this->at.'type' => 'Product',
            'name' => $product->name,
            'sku' => $product->sku,
            'brand' => $product->brand ? [$this->at.'type' => 'Brand', 'name' => $product->brand] : null,
            'description' => $product->short_description ?: str($product->description ?? '')->stripTags()->limit(300)->toString(),
            'image' => $product->image_url,
            'category' => $product->category?->name,
            'url' => route('shop.product', $product->slug),
            'offers' => ($showPrices && $product->hasPrice()) ? [
                $this->at.'type' => 'Offer',
                'price' => number_format((float) $product->price, 2, '.', ''),
                'priceCurrency' => $product->currency,
                'availability' => match ($product->stock_status) {
                    'out_of_stock' => 'https://schema.org/OutOfStock',
                    'on_order' => 'https://schema.org/PreOrder',
                    default => 'https://schema.org/InStock',
                },
                'url' => route('shop.product', $product->slug),
                'seller' => [$this->at.'type' => 'Organization', 'name' => setting('company.name', 'WestBridge Technologies')],
            ] : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function articleSchema(Post $post): array
    {
        $org = [
            $this->at.'type' => 'Organization',
            'name' => setting('company.name', 'WestBridge Technologies'),
            'logo' => [$this->at.'type' => 'ImageObject', 'url' => asset('brand/icon-512.png')],
        ];

        return $this->clean([
            $this->at.'context' => 'https://schema.org',
            $this->at.'type' => 'NewsArticle',
            'headline' => str($post->title)->limit(110)->toString(),
            'description' => $post->summary(200),
            'image' => $post->coverUrl(),
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'author' => $post->author
                ? [$this->at.'type' => 'Person', 'name' => $post->author->name]
                : [$this->at.'type' => 'Organization', 'name' => setting('company.name', 'WestBridge Technologies')],
            'publisher' => $org,
            'mainEntityOfPage' => route('news.show', $post->slug),
        ]);
    }

    /**
     * Every public, indexable URL with its last change date.
     *
     * @return list<array{loc: string, lastmod: ?string, priority: string}>
     */
    public function sitemapEntries(): array
    {
        $entries = [];
        $add = function (string $loc, $lastmod = null, string $priority = '0.6') use (&$entries): void {
            $entries[] = ['loc' => $loc, 'lastmod' => $lastmod ? \Illuminate\Support\Carbon::parse($lastmod)->toAtomString() : null, 'priority' => $priority];
        };

        $add(route('home'), null, '1.0');
        foreach (['about', 'services.index', 'services.software', 'services.networking', 'services.starlink', 'portfolio.index', 'shop.index', 'contact'] as $name) {
            $add(route($name), null, '0.8');
        }

        foreach ($this->portfolio->all() as $project) {
            $add(route('portfolio.show', $project['slug']), $project['updated_at'] ?? null, '0.7');
        }

        if (Schema::hasTable('products')) {
            ProductCategory::query()->visible()->whereHas('products', fn ($q) => $q->published())->get(['slug', 'updated_at'])
                ->each(fn ($c) => $add(route('shop.category', $c->slug), $c->updated_at, '0.6'));

            Product::query()->published()->get(['slug', 'updated_at'])
                ->each(fn ($p) => $add(route('shop.product', $p->slug), $p->updated_at, '0.6'));
        }

        if (Schema::hasTable('posts')) {
            if (Post::query()->live()->exists()) {
                $add(route('news.index'), Post::query()->live()->max('published_at'), '0.7');
            }

            Post::query()->live()->get(['slug', 'updated_at'])
                ->each(fn ($p) => $add(route('news.show', $p->slug), $p->updated_at, '0.6'));
        }

        foreach (['legal.privacy', 'legal.terms', 'legal.warranty'] as $name) {
            $add(route($name), null, '0.3');
        }

        return $entries;
    }

    /** Drop empty values recursively so the markup carries no blank fields. */
    private function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->clean($value);
                $data[$key] = $value;
            }

            $onlyType = is_array($value) && array_keys($value) === [$this->at.'type'];

            if ($value === null || $value === '' || $value === [] || $onlyType) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
