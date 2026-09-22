<?php

declare(strict_types=1);

use App\Services\Platform\SettingsService;

if (! function_exists('setting')) {
    /**
     * Read a configured setting. Returns $default when the setting is blank,
     * which is how templates decide whether to render an element at all.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsService::class)->get($key, $default);
    }
}

if (! function_exists('setting_service')) {
    /**
     * The settings repository itself, for writing.
     * Until the admin panel exists (Phase 7) this is how values are set:
     *   php artisan tinker
     *   >>> setting_service()->set('contact.phone', '+211 ...');
     */
    function setting_service(): SettingsService
    {
        return app(SettingsService::class);
    }
}

if (! function_exists('active_socials')) {
    /**
     * Social networks are shown ONLY once their URL is configured in admin.
     * Never render an icon that links nowhere.
     *
     * @return array<string, string>
     */
    function active_socials(): array
    {
        return app(SettingsService::class)
            ->group('social')
            ->filter(fn ($url): bool => filled($url))
            ->all();
    }
}

if (! function_exists('whatsapp_url')) {
    /**
     * Build a wa.me link from the configured number, optionally pre-filled.
     * Strips everything but digits — wa.me rejects spaces, plus signs and dashes.
     */
    function whatsapp_url(?string $message = null): ?string
    {
        $number = setting('contact.whatsapp');

        if (blank($number)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $number);
        $text = $message ?? setting('contact.whatsapp_default_message', 'Hello WestBridge, I would like to enquire about');

        return 'https://wa.me/'.$digits.'?text='.rawurlencode((string) $text);
    }
}

if (! function_exists('safe_map_embed')) {
    /**
     * The configured Google Maps embed address, or null. Only a real
     * https://www.google.com/maps/embed?... link is ever put in the iframe -
     * anything else (javascript:, another site) is ignored.
     */
    function safe_map_embed(): ?string
    {
        $url = trim((string) setting('contact.map_embed'));

        // People often paste the whole <iframe> snippet - take its src.
        if (preg_match('/src=["\']([^"\']+)["\']/i', $url, $m)) {
            $url = html_entity_decode($m[1]);
        }

        return preg_match('#^https://(www\.)?google\.com/maps/embed\?[^\s"\'<>]*$#i', $url) ? $url : null;
    }
}
