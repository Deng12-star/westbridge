<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use App\Services\Content\PortfolioRepository;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(PortfolioRepository $portfolio): View
    {
        return view('pages.home', [
            'capabilities' => config('westbridge.capabilities'),
            // The Our Projects block renders only with at least three entries.
            'projects' => $portfolio->all()->take(3)->all(),
            'featuredProducts' => Schema::hasTable('products')
                ? Product::query()->published()->with('category:id,name')->ordered()->limit(8)->get()
                : collect(),
            'latestPosts' => Schema::hasTable('posts')
                ? Post::query()->live()->latestFirst()->limit(3)->get()
                : collect(),
        ]);
    }
}
