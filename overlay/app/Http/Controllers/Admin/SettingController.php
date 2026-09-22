<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Platform\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** Tabs shown in admin, in order. Groups not listed here are not editable. */
    public const GROUPS = [
        'contact' => 'Contact details',
        'social' => 'Social media',
        'company' => 'Company',
        'shop' => 'Shop',
        'seo' => 'Search & analytics',
        'security' => 'Security',
    ];

    /** Settings that belong to features no longer on the site. */
    private const HIDDEN = ['shop.currency', 'shop.tax_rate', 'shop.tax_label', 'shop.low_stock_threshold', 'company.tin'];

    public function edit(?string $group = null): View
    {
        $group ??= 'contact';
        abort_unless(array_key_exists($group, self::GROUPS), 404);

        return view('admin.settings.edit', [
            'groups' => self::GROUPS,
            'group' => $group,
            'settings' => $this->settings($group),
        ]);
    }

    public function update(Request $request, string $group, SettingsService $service): RedirectResponse
    {
        abort_unless(array_key_exists($group, self::GROUPS), 404);

        $settings = $this->settings($group);
        $rules = [];

        foreach ($settings as $s) {
            $rules['values.'.$s->key] = match ($s->type) {
                'email' => ['nullable', 'email', 'max:190'],
                'url' => ['nullable', 'url:http,https', 'max:500'],
                'number' => ['nullable', 'numeric'],
                'boolean' => ['nullable', 'boolean'],
                'phone' => ['nullable', 'string', 'max:40', 'regex:/^[0-9+()\-\s]*$/'],
                'text' => ['nullable', 'string', 'max:5000'],
                default => ['nullable', 'string', 'max:500'],
            };
        }

        if ($group === 'security') {
            $rules['values.admin_lock_minutes'] = ['required', 'integer', 'min:5', 'max:120'];
            $rules['values.admin_logout_minutes'] = ['required', 'integer', 'min:10', 'max:480', 'gt:values.admin_lock_minutes'];
        }

        if ($group === 'contact') {
            $rules['values.map_embed'] = ['nullable', 'string', 'max:2000', function (string $attr, mixed $value, \Closure $fail): void {
                $url = (string) $value;
                if (preg_match('/src=["\']([^"\']+)["\']/i', $url, $m)) {
                    $url = html_entity_decode($m[1]);
                }
                if (filled($value) && ! preg_match('#^https://(www\.)?google\.com/maps/embed\?#i', trim($url))) {
                    $fail('Paste the embed link from Google Maps (Share -> Embed a map). It starts with https://www.google.com/maps/embed');
                }
            }];
        }

        $data = $request->validate($rules, [
            'values.*.regex' => 'Use digits, spaces and + ( ) - only.',
            'values.admin_logout_minutes.gt' => 'The sign-out time must be longer than the lock time.',
        ]);

        foreach ($settings as $s) {
            $value = data_get($data, 'values.'.$s->key);
            $value = $s->type === 'boolean' ? ($request->boolean('values.'.$s->key) ? '1' : '0') : trim((string) $value);

            $s->update(['value' => $value]);
        }

        $service->flush();

        return back()->with('status', self::GROUPS[$group].' saved.');
    }

    /** @return \Illuminate\Support\Collection<int, Setting> */
    private function settings(string $group)
    {
        return Setting::query()
            ->where('group', $group)
            ->orderBy('id')
            ->get()
            ->reject(fn (Setting $s) => in_array($group.'.'.$s->key, self::HIDDEN, true))
            ->values();
    }
}
