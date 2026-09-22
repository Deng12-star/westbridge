<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Creates (or promotes) a Super Admin. This is how the first administrator is
 * made on the live server - no default account is ever seeded in production.
 */
class CreateAdmin extends Command
{
    protected $signature = 'wb:create-admin {--email=} {--name=}';

    protected $description = 'Create a WestBridge Super Admin account (or promote an existing one)';

    public function handle(): int
    {
        $email = $this->option('email') ?: text('Email address', required: true);
        $name = $this->option('name') ?: text('Full name', required: true);
        $pass = password('Password (at least 10 characters)', required: true);

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $pass],
            ['email' => ['required', 'email'], 'name' => ['required', 'max:120'], 'password' => [Password::min(10)]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->callSilently('db:seed', ['--class' => 'Database\\Seeders\\RoleSeeder', '--force' => true]);

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $pass, 'is_active' => true, 'email_verified_at' => now()],
        );
        $user->syncRoles(['Super Admin']);

        $this->info("Super Admin ready: {$email}. Sign in at ".route('login'));

        return self::SUCCESS;
    }
}
