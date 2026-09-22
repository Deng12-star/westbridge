<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Turns text typed into the admin into HTML that is safe to print unescaped.
 *
 * - Plain text (no tags): blank lines become paragraphs, single line breaks
 *   become <br>, everything is escaped.
 * - Text with tags: parsed by symfony/html-sanitizer (a real HTML parser)
 *   with a small formatting allow-list. Only if that package is missing does
 *   the regex fallback below run.
 */
final class SafeHtml
{
    private const ALLOWED = '<p><br><strong><b><em><i><u><ul><ol><li><a><h3><h4><blockquote>';

    public static function clean(?string $input): ?string
    {
        $text = trim((string) $input);

        if ($text === '') {
            return null;
        }

        if (! preg_match('/<\/?[a-z][a-z0-9]*[^<>]*>/i', $text)) {
            return collect(preg_split('/\R\s*\R/', $text) ?: [])
                ->map(fn ($p) => trim((string) $p))
                ->filter()
                ->map(fn ($p) => '<p>'.nl2br(e($p), false).'</p>')
                ->join("\n");
        }

        if (class_exists(\Symfony\Component\HtmlSanitizer\HtmlSanitizer::class)) {
            return self::sanitizer()->sanitize($text);
        }

        return self::fallback($text);
    }

    private static function sanitizer(): \Symfony\Component\HtmlSanitizer\HtmlSanitizer
    {
        static $sanitizer = null;

        if ($sanitizer === null) {
            $config = (new \Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig)
                ->allowElement('p')->allowElement('br')
                ->allowElement('strong')->allowElement('b')->allowElement('em')->allowElement('i')->allowElement('u')
                ->allowElement('ul')->allowElement('ol')->allowElement('li')
                ->allowElement('h3')->allowElement('h4')->allowElement('blockquote')
                ->allowElement('a', ['href'])
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->forceAttribute('a', 'rel', 'noopener');

            $sanitizer = new \Symfony\Component\HtmlSanitizer\HtmlSanitizer($config);
        }

        return $sanitizer;
    }

    /** Regex fallback, used only until symfony/html-sanitizer is installed. */
    private static function fallback(string $text): string
    {
        $html = strip_tags($text, self::ALLOWED);

        // Attributes that can run script or restyle the page.
        $html = (string) preg_replace('/(?<=[\s\/"\'])(on\w+|style|class|id)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        // Links may only point at http(s), mail or phone.
        $html = (string) preg_replace_callback('/(?<=[\s\/"\'])href\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', function (array $m): string {
            $raw = ($m[2] ?? '') !== '' ? $m[2] : ((($m[3] ?? '') !== '') ? $m[3] : ($m[4] ?? ''));
            $url = trim(html_entity_decode($raw, ENT_QUOTES));
            // Strip control characters and spaces that browsers ignore inside "java script:".
            $check = strtolower((string) preg_replace('/[\x00-\x20]+/', '', $url));

            return preg_match('#^(https?:|mailto:|tel:|/|\#)#', $check) ? 'href="'.e($url).'" rel="noopener"' : '';
        }, $html);

        return $html;
    }
}
