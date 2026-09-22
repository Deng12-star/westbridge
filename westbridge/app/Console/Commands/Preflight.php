<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\LocalAdminSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Checks the live configuration before a deploy is allowed to continue.
 * scripts/deploy.sh stops if this reports a failure.
 */
class Preflight extends Command
{
    protected $signature = 'wb:preflight';

    protected $description = 'Check that this installation is safe to run as the live site';

    /** @var list<string> */
    private array $failures = [];

    /** @var list<string> */
    private array $warnings = [];

    public function handle(): int
    {
        $this->check(app()->environment('production'), 'APP_ENV must be "production" (it is "'.app()->environment().'").');
        $this->check(! config('app.debug'), 'APP_DEBUG must be false - debug pages show passwords and file paths.');
        $this->check(filled(config('app.key')), 'APP_KEY is missing. Run: php artisan key:generate');
        $this->check(str_starts_with((string) config('app.url'), 'https://'), 'APP_URL must start with https://');
        $this->check(! in_array(config('mail.default'), ['log', 'array'], true), 'MAIL_MAILER is "'.config('mail.default').'" - no email would ever be sent. Set up SMTP.');

        $from = (string) config('mail.from.address');
        $this->check(filled($from) && $from !== 'hello@example.com' && ! str_ends_with($from, '.local'), 'MAIL_FROM_ADDRESS is not set. Set it to an address on your domain, e.g. website@your-domain.com');
        $this->warn_if(config('database.default') === 'sqlite', 'The database is SQLite. MySQL is recommended for the live site.');
        $this->warn_if(! config('session.secure'), 'SESSION_SECURE_COOKIE is not true.');
        $this->warn_if(config('logging.default') === 'stack' && config('logging.channels.stack.channels') === ['single'], 'Logs go to one file that never rotates. Set LOG_STACK=daily.');

        try {
            if (Schema::hasTable('users')) {
                $this->check(
                    ! User::query()->where('email', LocalAdminSeeder::EMAIL)->exists(),
                    'The local test account '.LocalAdminSeeder::EMAIL.' exists. Delete it: it has a published password.'
                );

                $weak = User::query()->where('is_active', true)->get()
                    ->filter(fn (User $u) => Hash::check('password', $u->password));
                $this->check($weak->isEmpty(), 'These accounts use the password "password": '.$weak->pluck('email')->join(', '));
            }
        } catch (Throwable $e) {
            $this->failures[] = 'Could not reach the database: '.$e->getMessage();
        }

        foreach ($this->warnings as $w) {
            $this->warn('  ! '.$w);
        }
        foreach ($this->failures as $f) {
            $this->error('  x '.$f);
        }

        if ($this->failures !== []) {
            $this->newLine();
            $this->error('Preflight failed - fix the items above before this goes live.');

            return self::FAILURE;
        }

        $this->info('Preflight passed.');

        return self::SUCCESS;
    }

    private function check(bool $ok, string $message): void
    {
        if (! $ok) {
            $this->failures[] = $message;
        }
    }

    private function warn_if(bool $condition, string $message): void
    {
        if ($condition) {
            $this->warnings[] = $message;
        }
    }
}
