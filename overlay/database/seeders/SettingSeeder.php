<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\Platform\SettingsService;
use Illuminate\Database\Seeder;

/**
 * Contact details, social URLs and tax rates are seeded EMPTY on purpose.
 *
 * Nothing about WestBridge is invented in code. Every blank value below is
 * filled by the client from the admin panel, and each front-end element that
 * depends on one hides itself until it is set.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // group, key, value, type, label
            ['company', 'name', 'WestBridge Technologies', 'string', 'Company name'],
            ['company', 'legal_name', '', 'string', 'Registered legal name'],
            ['company', 'tagline', 'Connecting Ideas. Building Tomorrow.', 'string', 'Tagline'],
            ['company', 'short_description', 'Technology company building software, delivering IT infrastructure and connectivity, and supplying technology products in South Sudan.', 'text', 'Short description'],
            ['company', 'tin', '', 'string', 'Tax identification number (appears on invoices)'],

            // --- Awaiting real values from the client -----------------------
            ['contact', 'phone', '', 'phone', 'Primary phone number'],
            ['contact', 'phone_alt', '', 'phone', 'Alternative phone number'],
            ['contact', 'whatsapp', '', 'phone', 'WhatsApp number (floating button)'],
            ['contact', 'whatsapp_default_message', 'Hello WestBridge, I would like to enquire about', 'string', 'Default WhatsApp message'],
            ['contact', 'email', '', 'email', 'General enquiries email'],
            ['contact', 'email_sales', '', 'email', 'Sales email'],
            ['contact', 'email_support', '', 'email', 'Support email'],
            ['contact', 'address', '', 'text', 'Street address'],
            ['contact', 'city', 'Juba', 'string', 'City'],
            ['contact', 'country', 'South Sudan', 'string', 'Country'],
            ['contact', 'hours', '', 'text', 'Business hours'],
            ['contact', 'map_embed', '', 'text', 'Google Maps embed URL'],

            // Social icons render ONLY when a URL is present.
            ['social', 'facebook', '', 'url', 'Facebook page URL'],
            ['social', 'linkedin', '', 'url', 'LinkedIn page URL'],
            ['social', 'instagram', '', 'url', 'Instagram profile URL'],
            ['social', 'twitter', '', 'url', 'X / Twitter profile URL'],
            ['social', 'youtube', '', 'url', 'YouTube channel URL'],

            ['seo', 'default_title', 'WestBridge Technologies — Connecting Ideas. Building Tomorrow.', 'string', 'Default page title'],
            ['seo', 'default_description', 'WestBridge Technologies builds software, delivers IT infrastructure and connectivity, and supplies technology products in Juba, South Sudan.', 'text', 'Default meta description'],
            ['seo', 'google_analytics_id', '', 'string', 'Analytics measurement ID (dashboard widget hides when blank)'],
            ['seo', 'google_site_verification', '', 'string', 'Search Console verification token'],

            ['shop', 'currency', 'USD', 'string', 'Transacting currency (decision D2)'],
            ['shop', 'display_currency', 'SSP', 'string', 'Secondary display currency'],
            ['shop', 'exchange_rate', '0', 'number', 'USD to SSP rate used for display'],
            // Confirm the applicable rate with your accountant before invoicing.
            ['shop', 'tax_rate', '0', 'number', 'Sales tax rate (%) — CONFIRM BEFORE GOING LIVE'],
            ['shop', 'tax_label', 'Sales Tax', 'string', 'Tax label shown on documents'],
            ['shop', 'low_stock_threshold', '5', 'number', 'Low stock warning threshold'],
            ['shop', 'show_prices', '1', 'boolean', 'Show prices on the shop (off = every item says "Price on request")'],
            ['shop', 'intro', 'Laptops, phones, networking and Starlink equipment. See something you need? Message us on WhatsApp and we will confirm price and availability.', 'text', 'Shop page introduction'],

            // Admin session protection (enforced by AdminSessionGuard).
            ['security', 'admin_lock_minutes', '15', 'number', 'Lock the admin screen after this many minutes without activity (5 to 120)'],
            ['security', 'admin_logout_minutes', '60', 'number', 'Sign out completely after this many minutes without activity (10 to 480)'],

            ['documents', 'quotation_validity_days', '30', 'number', 'Default quotation validity'],
            ['documents', 'quotation_terms', '', 'text', 'Default quotation terms'],
            ['documents', 'invoice_terms', '', 'text', 'Default invoice terms'],
            ['documents', 'invoice_due_days', '14', 'number', 'Default invoice payment terms (days)'],
            ['documents', 'receipt_footer', 'Thank you for your business.', 'string', 'Receipt thank-you message'],
            ['documents', 'bank_details', '', 'text', 'Bank transfer details shown at checkout and on invoices'],
        ];

        // Safe to run again: an existing value is never overwritten - only a
        // missing setting is created, and labels/types are kept current.
        foreach ($settings as [$group, $key, $value, $type, $label]) {
            $setting = Setting::query()->firstOrNew(['group' => $group, 'key' => $key]);

            if (! $setting->exists) {
                $setting->value = $value;
            }

            $setting->fill(['type' => $type, 'label' => $label])->save();
        }

        app(SettingsService::class)->flush();
    }
}
