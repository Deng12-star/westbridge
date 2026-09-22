<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Seo\SeoService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap(SeoService $seo): Response
    {
        // Rebuilt at most every 30 minutes; cheap either way.
        $xml = Cache::remember('seo.sitemap', now()->addMinutes(30), function () use ($seo): string {
            $rows = collect($seo->sitemapEntries())->map(function (array $e): string {
                $lastmod = $e['lastmod'] ? '<lastmod>'.$e['lastmod'].'</lastmod>' : '';

                return '<url><loc>'.e($e['loc']).'</loc>'.$lastmod.'<priority>'.$e['priority'].'</priority></url>';
            })->join("\n");

            return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".$rows."\n</urlset>\n";
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = app()->environment('production')
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /search', 'Disallow: /product/*/whatsapp', '', 'Sitemap: '.route('sitemap')]
            // Anything that is not the live site stays out of search results.
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
