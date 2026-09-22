<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SettingSeeder::class,
            PageSeeder::class,
            CatalogueSeeder::class,
            SecurityCategoriesSeeder::class,
            ProjectSeeder::class,
        ]);

        // Every seeder above is create-only, so `php artisan db:seed` is safe to
        // run again after an update - it adds what is new and edits nothing.
        //
        // No user account is created here. On your PC, RUN-ME.bat calls
        // LocalAdminSeeder; on the live server the first administrator is made
        // with `php artisan wb:create-admin`.
    }
}
