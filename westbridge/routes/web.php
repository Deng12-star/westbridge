<?php

declare(strict_types=1);

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\NewsController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PlaceholderController;
use App\Http\Controllers\Web\PortfolioController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\SeoController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
| Every URL from the approved sitemap is registered from Phase 1 so the
| navigation is fully clickable during review and no internal link 404s.
| Routes still on the placeholder controller are annotated with the phase
| that replaces them.
*/

Route::get('/', HomeController::class)->name('home');

// --- Corporate ------------------------------------------------------ built
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
// Quote requests are retired from the site: enquiries go through Contact.
// The route stays (as a permanent redirect) so old links and bookmarks land
// somewhere useful instead of on a 404.
Route::permanentRedirect('/quote', '/contact')->name('quote');

// --- Services ----------------------------------------------------- built
Route::get('/services', [PageController::class, 'services'])->name('services.index');
Route::get('/services/software-development', [ServiceController::class, 'software'])->name('services.software');
Route::get('/services/it-networking', [ServiceController::class, 'networking'])->name('services.networking');
Route::get('/services/starlink', [ServiceController::class, 'starlink'])->name('services.starlink');
Route::get('/services/cctv-intercom', [ServiceController::class, 'cctv'])->name('services.cctv');

// --- Work -------------------------------------------------------- Phase 3
Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Route::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
Route::get('/projects', [PlaceholderController::class, 'projects'])->name('projects.index');

// --- News --------------------------------------------------------- built
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [NewsController::class, 'show'])->name('news.show');
// The blueprint called this section Insights; old links redirect.
Route::permanentRedirect('/insights', '/news')->name('insights.index');
Route::get('/insights/{slug}', [NewsController::class, 'legacy'])->name('insights.show');

// --- Shop ------------------------------------------------------- built
// A catalogue: products are shown, and ordering happens on WhatsApp.
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{category}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/product/{slug}', [ShopController::class, 'show'])->name('shop.product');
Route::get('/product/{slug}/whatsapp', [ShopController::class, 'whatsapp'])
    ->middleware('throttle:30,1')->name('shop.whatsapp');

// Online checkout is not part of the site. These addresses stay so that any
// old link lands in the shop rather than on a 404.
Route::permanentRedirect('/cart', '/shop')->name('cart');
Route::permanentRedirect('/checkout', '/shop')->name('checkout');
Route::permanentRedirect('/track', '/contact')->name('order.track');

// --- Search ------------------------------------------------------- built
Route::get('/search', SearchController::class)->middleware('throttle:60,1')->name('search');

// --- SEO ---------------------------------------------------------- built
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// --- Legal ---------------------------------------------------------- built
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('legal.privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('legal.terms');
Route::get('/warranty-returns', [PageController::class, 'warranty'])->name('legal.warranty');

// --- Admin panel -------------------------------------------------- built
require __DIR__.'/admin.php';
