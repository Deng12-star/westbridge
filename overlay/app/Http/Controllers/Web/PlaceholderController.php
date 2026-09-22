<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

/**
 * Pages not yet written. Every URL in the sitemap exists so no link 404s;
 * each method is replaced by its real controller when that page is built.
 *
 * Real methods (not route closures) so `php artisan route:cache` works.
 */
class PlaceholderController extends Controller
{
    public function projects(): View
    {
        return $this->show('Projects', '3');
    }

    private function show(string $title, string $phase): View
    {
        return view('pages.placeholder', ['title' => $title, 'phase' => $phase]);
    }
}
