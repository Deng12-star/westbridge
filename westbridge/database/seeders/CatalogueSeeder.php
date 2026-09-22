<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProductCategory;
use Database\Seeders\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The store's starter categories, as listed in the original brief. No products are
 * seeded - every item in the shop is one WestBridge actually stocks, added
 * from the admin panel.
 */
class CatalogueSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        // Once only: a category deleted in the admin must not come back.
        $this->seedOnce('categories_seeded', ProductCategory::class, fn () => $this->seed());
    }

    private function seed(): void
    {
        $categories = ['Laptops', 'Phones', 'Accessories', 'Networking', 'Starlink', 'Computer Accessories', 'CCTV Cameras', 'Intercom Systems'];

        foreach ($categories as $i => $name) {
            ProductCategory::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $i + 1, 'is_visible' => true],
            );
        }
    }
}
