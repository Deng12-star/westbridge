<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\SafeHtml;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Text editing for the CMS pages (About, Services, Contact, legal).
 *
 * Page layout stays in the templates; this edits the words. Each block is
 * shown in the form that fits its shape: a text box for a paragraph, and a
 * list of title/description pairs for things like Values.
 */
class PageController extends Controller
{
    /** Blocks the templates read but the seeder may not have created yet. */
    private const EXPECTED_BLOCKS = [
        'about' => ['story', 'who_we_are', 'what_we_do', 'mission', 'vision', 'why_technology', 'values', 'approach'],
        'privacy-policy' => ['body'],
        'terms' => ['body'],
        'warranty-returns' => ['body'],
    ];

    private const LIST_BLOCKS = ['values', 'approach'];

    /** Printed escaped by the templates, so kept as plain text. */
    private const PLAIN_BLOCKS = ['mission', 'vision'];

    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::query()->where('slug', '!=', 'quote')->orderBy('title')->get(),
        ]);
    }

    public function edit(Page $page): View
    {
        $blocks = (array) ($page->blocks ?? []);

        foreach (self::EXPECTED_BLOCKS[$page->slug] ?? [] as $key) {
            $blocks[$key] ??= in_array($key, self::LIST_BLOCKS, true) ? [] : null;
        }

        return view('admin.pages.form', [
            'page' => $page,
            'blocks' => $blocks,
            'listBlocks' => self::LIST_BLOCKS,
            'publicUrl' => $this->publicUrl($page->slug),
        ]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'heading' => ['nullable', 'string', 'max:255'],
            'lede' => ['nullable', 'string', 'max:2000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'blocks' => ['nullable', 'array'],
            'blocks.*' => ['nullable'],
        ]);

        $blocks = (array) ($page->blocks ?? []);

        foreach ((array) ($data['blocks'] ?? []) as $key => $value) {
            if (! is_string($key) || ! preg_match('/^[a-z_]+$/', $key)) {
                continue;
            }

            if (is_array($value)) {
                // Title/description rows - keep only rows with a title.
                $blocks[$key] = collect($value)
                    ->map(fn ($row) => ['title' => trim((string) data_get($row, 'title')), 'body' => trim((string) data_get($row, 'body'))])
                    ->filter(fn ($row) => $row['title'] !== '')
                    ->values()
                    ->all();
            } else {
                $text = mb_substr(trim((string) $value), 0, 20000);
                $blocks[$key] = in_array($key, self::PLAIN_BLOCKS, true)
                    ? ($text === '' ? null : strip_tags($text))
                    : SafeHtml::clean($text);
            }
        }

        $page->update([
            'heading' => $data['heading'] ?? null,
            'lede' => $data['lede'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'blocks' => $blocks,
        ]);

        return back()->with('status', 'Page saved.');
    }

    private function publicUrl(string $slug): ?string
    {
        return match ($slug) {
            'about' => route('about'),
            'services' => route('services.index'),
            'contact' => route('contact'),
            'privacy-policy' => route('legal.privacy'),
            'terms' => route('legal.terms'),
            'warranty-returns' => route('legal.warranty'),
            default => null,
        };
    }
}
