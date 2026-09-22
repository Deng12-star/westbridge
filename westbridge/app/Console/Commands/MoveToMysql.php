<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Moves the site's data from the SQLite file into MySQL (e.g. XAMPP), then
 * points the site at MySQL.
 *
 * Safe by design:
 *  - the SQLite file is only read, never changed - it stays as a backup;
 *  - every table's row count is compared before anything is switched;
 *  - .env is copied to .env.before-mysql first, so going back is one copy;
 *  - it refuses to write into a MySQL database that already has tables.
 */
class MoveToMysql extends Command
{
    protected $signature = 'wb:move-to-mysql
        {--host=127.0.0.1} {--port=3306} {--database=westbridge}
        {--username=root} {--password=}';

    protected $description = 'Copy all data from SQLite into MySQL and switch the site to MySQL';

    private const TARGET = 'wb_mysql';

    public function handle(): int
    {
        $source = config('database.default');

        if (config("database.connections.{$source}.driver") !== 'sqlite') {
            $this->info('The site is already using '.config("database.connections.{$source}.driver").'. Nothing to do.');

            return self::SUCCESS;
        }

        $opts = [
            'host' => (string) $this->option('host'),
            'port' => (string) $this->option('port'),
            'database' => (string) $this->option('database'),
            'username' => (string) $this->option('username'),
            'password' => (string) $this->option('password'),
        ];

        if (! preg_match('/^[A-Za-z0-9_]+$/', $opts['database'])) {
            $this->error('Database name may contain only letters, numbers and underscores.');

            return self::FAILURE;
        }

        try {
            $this->step('Connecting to MySQL at '.$opts['host'].':'.$opts['port']);
            $this->createDatabase($opts);
            $this->registerTarget($opts);

            $this->step('Creating the tables in "'.$opts['database'].'"');
            $exit = Artisan::call('migrate', ['--database' => self::TARGET, '--force' => true]);
            if ($exit !== 0) {
                throw new RuntimeException("Creating the tables failed:\n".Artisan::output());
            }

            $this->step('Copying the data');
            $counts = $this->copyAll($source);

            $this->step('Checking every table');
            $this->verify($source, $counts);

            $this->step('Switching the site to MySQL');
            $this->switchEnv($opts);
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('Stopped: '.$e->getMessage());
            $this->line('The site was NOT switched - it is still using the SQLite file, unchanged.');

            return self::FAILURE;
        }

        Artisan::call('config:clear');
        // Clear the cache in the NEW database (this process still has the old
        // connection as its default).
        DB::connection(self::TARGET)->table('cache')->delete();
        DB::connection(self::TARGET)->table('cache_locks')->delete();

        $this->newLine();
        $this->info('Done. The site now uses the MySQL database "'.$opts['database'].'" - open phpMyAdmin to see it.');
        $this->line('The old SQLite file is kept as a backup: database/database.sqlite');
        $this->line('To go back: copy .env.before-mysql over .env');

        return self::SUCCESS;
    }

    /** @param array<string, string> $o */
    private function createDatabase(array $o): void
    {
        try {
            $pdo = new PDO("mysql:host={$o['host']};port={$o['port']}", $o['username'], $o['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        } catch (Throwable $e) {
            throw new RuntimeException("Could not reach MySQL. Is it started in the XAMPP Control Panel?\n  (".$e->getMessage().')');
        }

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$o['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $tables = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ".$pdo->quote($o['database']))->fetchColumn();
        if ((int) $tables > 0) {
            throw new RuntimeException("The MySQL database \"{$o['database']}\" already has tables in it. Nothing was overwritten. Use a new name (--database=westbridge2) or empty it in phpMyAdmin first.");
        }
    }

    /** @param array<string, string> $o */
    private function registerTarget(array $o): void
    {
        config(['database.connections.'.self::TARGET => array_merge(config('database.connections.mysql'), [
            'driver' => 'mysql',
            'host' => $o['host'],
            'port' => $o['port'],
            'database' => $o['database'],
            'username' => $o['username'],
            'password' => $o['password'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ])]);

        DB::purge(self::TARGET);
    }

    /** @return array<string, int> rows copied per table */
    private function copyAll(string $source): array
    {
        $from = DB::connection($source);
        $to = DB::connection(self::TARGET);

        $tables = collect($from->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
            ->pluck('name')
            // migrations: written by migrate above. cache/cache_locks: SQLite
            // stores cached values base64-encoded and MySQL does not, so a
            // copied cache cannot be read - it rebuilds itself anyway.
            ->reject(fn ($t) => in_array($t, ['migrations', 'cache', 'cache_locks'], true))
            ->values();

        $targetTables = collect($to->select('SHOW TABLES'))->map(fn ($r) => array_values((array) $r)[0])->all();
        $counts = [];

        $to->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $table) {
                if (! in_array($table, $targetTables, true)) {
                    $this->warn("  skipped {$table} (not part of the site's tables)");

                    continue;
                }

                $columns = collect($to->select("SHOW COLUMNS FROM `{$table}`"))->pluck('Field')->all();
                $copied = 0;

                $from->table($table)->orderByRaw('rowid')->chunk(200, function ($rows) use ($to, $table, $columns, &$copied): void {
                    $batch = $rows->map(fn ($r) => array_intersect_key((array) $r, array_flip($columns)))->all();
                    $to->table($table)->insert($batch);
                    $copied += count($batch);
                });

                $counts[$table] = $copied;
                $this->line(sprintf('  %-28s %6d rows', $table, $copied));
            }
        } finally {
            $to->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return $counts;
    }

    /** @param array<string, int> $counts */
    private function verify(string $source, array $counts): void
    {
        foreach ($counts as $table => $copied) {
            $inSource = DB::connection($source)->table($table)->count();
            $inTarget = DB::connection(self::TARGET)->table($table)->count();

            if ($inSource !== $inTarget) {
                throw new RuntimeException("Table {$table}: {$inSource} rows in SQLite but {$inTarget} in MySQL.");
            }
        }

        $this->line('  all '.count($counts).' tables match');
    }

    /** @param array<string, string> $o */
    private function switchEnv(array $o): void
    {
        $path = base_path('.env');
        $env = (string) File::get($path);
        File::copy($path, base_path('.env.before-mysql'));

        $values = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $o['host'],
            'DB_PORT' => $o['port'],
            'DB_DATABASE' => $o['database'],
            'DB_USERNAME' => $o['username'],
            'DB_PASSWORD' => $o['password'],
        ];

        foreach ($values as $key => $value) {
            $line = $key.'='.$value;
            // Replace the key whether it is set or commented out (# DB_HOST=...).
            $env = preg_match("/^#?\s*{$key}=.*$/m", $env)
                ? (string) preg_replace("/^#?\s*{$key}=.*$/m", $line, $env, 1)
                : rtrim($env)."\n".$line."\n";
        }

        File::put($path, $env);
    }

    private function step(string $text): void
    {
        $this->newLine();
        $this->info('> '.$text);
    }
}
