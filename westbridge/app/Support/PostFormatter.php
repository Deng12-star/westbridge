<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Turns what staff type in the news editor into HTML.
 *
 * Written for people who do not know HTML:
 *
 *   A blank line          starts a new paragraph
 *   ## Heading            a section heading
 *   - item  (or * item)   a bullet list (one item per line)
 *   1. item               a numbered list
 *   > quote               a quotation
 *   **bold**              bold text
 *   https://...           becomes a link
 *
 * Text that already contains HTML tags is passed to SafeHtml instead. Either
 * way the result goes through SafeHtml, so nothing unsafe reaches the page.
 */
final class PostFormatter
{
    public static function toHtml(?string $text): ?string
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", (string) $text));

        if ($text === '') {
            return null;
        }

        if (preg_match('/<\/?[a-z][a-z0-9]*[^<>]*>/i', $text)) {
            return SafeHtml::clean($text);
        }

        $html = collect(preg_split('/\n\s*\n/', $text) ?: [])
            ->map(fn ($block) => self::block(trim((string) $block)))
            ->filter()
            ->join("\n");

        return SafeHtml::clean($html);
    }

    private static function block(string $block): string
    {
        if ($block === '') {
            return '';
        }

        $lines = array_map('trim', explode("\n", $block));

        if (preg_match('/^###\s+(.+)$/s', $block, $m)) {
            return '<h4>'.self::inline($m[1]).'</h4>';
        }

        if (preg_match('/^##?\s+(.+)$/s', $block, $m)) {
            return '<h3>'.self::inline($m[1]).'</h3>';
        }

        if (self::every($lines, '/^[-*•]\s+/')) {
            return '<ul>'.collect($lines)->map(fn ($l) => '<li>'.self::inline((string) preg_replace('/^[-*•]\s+/', '', $l)).'</li>')->join('').'</ul>';
        }

        if (self::every($lines, '/^\d+[.)]\s+/')) {
            return '<ol>'.collect($lines)->map(fn ($l) => '<li>'.self::inline((string) preg_replace('/^\d+[.)]\s+/', '', $l)).'</li>')->join('').'</ol>';
        }

        if (self::every($lines, '/^>\s?/')) {
            return '<blockquote><p>'.collect($lines)->map(fn ($l) => self::inline((string) preg_replace('/^>\s?/', '', $l)))->join('<br>').'</p></blockquote>';
        }

        return '<p>'.collect($lines)->map(fn ($l) => self::inline($l))->join('<br>').'</p>';
    }

    /** Escapes the text, then applies **bold** and turns web addresses into links. */
    private static function inline(string $text): string
    {
        $text = e($text);
        $text = (string) preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);

        return (string) preg_replace_callback(
            '#\bhttps?://[^\s<]+[^\s<.,;:!?)\]\'"]#i',
            fn ($m) => '<a href="'.$m[0].'">'.$m[0].'</a>',
            $text,
        );
    }

    /** @param list<string> $lines */
    private static function every(array $lines, string $pattern): bool
    {
        foreach ($lines as $line) {
            if (! preg_match($pattern, $line)) {
                return false;
            }
        }

        return $lines !== [];
    }
}
