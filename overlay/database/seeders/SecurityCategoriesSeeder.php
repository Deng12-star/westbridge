<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Adds the CCTV and intercom shop categories to an installation that was set
 * up before they existed. Runs once: if a category is later deleted in the
 * admin it is not brought back.
 */
class SecurityCategoriesSeeder extends Seeder
{
    private const MARKER = 'categories_security_seeded';

    public function run(): void
    {
        if (Setting::query()->where('group', 'system')->where('key', self::MARKER)->exists()) {
            return;
        }

        $next = (int) ProductCategory::query()->max('sort_order');

        foreach (['cctv-cameras' => 'CCTV Cameras', 'intercom-systems' => 'Intercom Systems'] as $slug => $name) {
            ProductCategory::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'sort_order' => ++$next, 'is_visible' => true],
            );
        }

        Setting::query()->create([
            'group' => 'system', 'key' => self::MARKER, 'value' => now()->toAtomString(),
            'type' => 'string', 'label' => 'Starter content seeded', 'is_public' => false,
        ]);
    }
}
