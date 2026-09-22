<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * The convenience account for working on your own PC:
 *   admin@westbridge.test / password
 *
 * Called ONLY by RUN-ME.bat and the local installers - never by
 * DatabaseSeeder, so no deploy can create it. It also refuses to run anywhere
 * that is not APP_ENV=local.
 */
class LocalAdminSeeder extends Seeder
{
    public const EMAIL = 'admin@westbridge.test';

    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('LocalAdminSeeder only runs with APP_ENV=local.');
        }

        $this->call(RoleSeeder::class);

        $admin = User::query()->firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'WestBridge Admin',
                'password' => Hash::make('password'),
                'job_title' => 'Super Admin',
                'email_verified_at' => now(),
            ],
        );

        $admin->assignRole('Super Admin');
    }
}
