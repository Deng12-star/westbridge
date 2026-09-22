<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Content\PortfolioRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

/** The service line pages, all driven by config/service_pages.php. */
class ServiceController extends Controller
{
    public function __construct(private readonly PortfolioRepository $portfolio) {}

    public function software(): View
    {
        return $this->show('software');
    }

    public function networking(): View
    {
        return $this->show('networking');
    }

    public function starlink(): View
    {
        return $this->show('starlink');
    }

    public function cctv(): View
    {
        return $this->show('cctv');
    }

    private function show(string $key): View
    {
        $service = config("service_pages.{$key}");
        abort_unless(is_array($service), 404);

        $categories = collect();
        $products = collect();

        if (filled($service['shop_categories']) && Schema::hasTable('products')) {
            $categories = ProductCategory::query()->visible()->whereIn('slug', $service['shop_categories'])->ordered()->get();
            $products = Product::query()->published()->with('category:id,name,slug')
                ->whereIn('product_category_id', $categories->pluck('id'))
                ->ordered()->limit(4)->get();
        }

        $projects = $this->portfolio->all()
            ->filter(fn ($p) => ($p['service'] ?? null) === $service['portfolio_service'])
            ->take(3)->values();

        return view('pages.service', [
            'key' => $key,
            'service' => $service,
            'process' => config('service_pages.process'),
            'categories' => $categories,
            'products' => $products,
            'projects' => $projects,
            'others' => collect(config('service_pages'))->except([$key, 'process']),
        ]);
    }
}
