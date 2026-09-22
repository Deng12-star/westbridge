<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Content\PortfolioRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, PortfolioRepository $portfolio): View
    {
        $term = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        $products = collect();
        $projects = collect();

        if (mb_strlen($term) >= 2) {
            $like = '%'.$term.'%';
            $products = Product::query()->published()
                ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('brand', 'like', $like)->orWhere('sku', 'like', $like)->orWhere('short_description', 'like', $like))
                ->ordered()->limit(24)->get();

            $needle = mb_strtolower($term);
            $projects = $portfolio->all()->filter(fn ($p) => str_contains(mb_strtolower($p['title'].' '.$p['summary'].' '.$p['category'].' '.$p['client']), $needle))->values();
        }

        return view('pages.search', compact('term', 'products', 'projects'));
    }
}
