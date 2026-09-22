<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\WhatsappClick;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The shop is a catalogue. Nobody pays on the site: every product page has
 * an "Order on WhatsApp" button that opens a chat with the product already
 * named, and the sale happens in that conversation.
 */
class ShopController extends Controller
{
    public function index(Request $request): View
    {
        return $this->listing($request, null);
    }

    public function category(Request $request, string $category): View
    {
        $model = ProductCategory::query()->visible()->where('slug', $category)->firstOrFail();

        return $this->listing($request, $model);
    }

    public function show(string $slug): View
    {
        $product = Product::query()->published()->with('category')->where('slug', $slug)->firstOrFail();

        $related = Product::query()->published()
            ->whereKeyNot($product->id)
            ->when($product->product_category_id, fn ($q) => $q->where('product_category_id', $product->product_category_id))
            ->ordered()
            ->limit(4)
            ->get();

        return view('pages.shop.show', [
            'product' => $product,
            'related' => $related,
            'whatsapp' => whatsapp_url($product->whatsappMessage()),
        ]);
    }

    /**
     * Counts the tap, then hands over to WhatsApp. The count is the only
     * record the site gets of what people are asking about.
     */
    public function whatsapp(string $slug): RedirectResponse
    {
        $product = Product::query()->published()->where('slug', $slug)->firstOrFail();
        $url = whatsapp_url($product->whatsappMessage());

        if ($url === null) {
            return redirect()->route('contact', ['product' => $product->name]);
        }

        WhatsappClick::query()->create(['product_id' => $product->id, 'page' => 'product']);

        return redirect()->away($url);
    }

    private function listing(Request $request, ?ProductCategory $category): View
    {
        $term = trim((string) $request->query('q', ''));

        $products = Product::query()->published()
            ->with('category:id,name,slug')
            ->when($category, fn ($q) => $q->where('product_category_id', $category->id))
            ->when($term !== '', fn ($q) => $q->where(function ($q) use ($term): void {
                $like = '%'.$term.'%';
                $q->where('name', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('short_description', 'like', $like);
            }))
            ->ordered()
            ->paginate(24)
            ->withQueryString();

        return view('pages.shop.index', [
            'products' => $products,
            'category' => $category,
            'categories' => ProductCategory::query()->visible()->ordered()
                ->withCount(['products' => fn ($q) => $q->published()])
                ->get(),
            'term' => $term,
            'totalProducts' => Product::query()->published()->count(),
        ]);
    }
}
