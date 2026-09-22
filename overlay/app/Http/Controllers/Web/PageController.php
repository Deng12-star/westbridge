<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\TeamMember;
use Illuminate\Contracts\View\View;

/**
 * CMS-backed pages. Content comes from the `pages` table so nothing here is
 * hard-coded copy; the template supplies structure and the record supplies words.
 */
class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'page' => $this->page('about'),
            'team' => \Illuminate\Support\Facades\Schema::hasTable('team_members')
                ? TeamMember::query()->published()->ordered()->get()
                : collect(),
        ]);
    }

    public function services(): View
    {
        return view('pages.services', [
            'page' => $this->page('services'),
            'solutions' => config('westbridge.navigation.solutions'),
            'businessAreas' => config('westbridge.business_areas'),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'page' => $this->page('contact'),
        ]);
    }

    public function quote(): View
    {
        return view('pages.quote', [
            'page' => $this->page('quote'),
        ]);
    }

    public function privacy(): View
    {
        return $this->legal('privacy-policy');
    }

    public function terms(): View
    {
        return $this->legal('terms');
    }

    public function warranty(): View
    {
        return $this->legal('warranty-returns');
    }

    public function legal(string $slug): View
    {
        return view('pages.legal', [
            'page' => $this->page($slug),
        ]);
    }

    /**
     * A missing or unpublished page renders the template with empty content
     * rather than a 500 — a half-configured CMS must never take the site down.
     */
    private function page(string $slug): Page
    {
        return Page::query()->published()->firstWhere('slug', $slug)
            ?? new Page(['slug' => $slug, 'title' => str($slug)->headline(), 'blocks' => []]);
    }
}
