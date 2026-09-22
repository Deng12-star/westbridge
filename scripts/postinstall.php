<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Post-install wiring
|--------------------------------------------------------------------------
| Run from inside the freshly created Laravel application, after the overlay
| has been copied over it:
|
|     php ../scripts/postinstall.php [--sqlite]
|
| Called by both install.sh and install.ps1 so the two installers cannot
| drift apart. Every step is idempotent - running it twice is harmless.
*/

$useSqlite = in_array('--sqlite', $argv, true);

function step(string $message): void
{
    echo "  - {$message}\n";
}

// ---------------------------------------------------------------------------
// 1. Register the platform service provider
// ---------------------------------------------------------------------------
$providers = 'bootstrap/providers.php';

if (is_file($providers)) {
    $contents = file_get_contents($providers);

    if (! str_contains($contents, 'WestBridgeServiceProvider')) {
        $contents = preg_replace(
            '/\];\s*$/',
            "    App\\\\Providers\\\\WestBridgeServiceProvider::class,\n];\n",
            $contents
        );
        file_put_contents($providers, $contents);
        step('Registered WestBridgeServiceProvider');
    } else {
        step('WestBridgeServiceProvider already registered');
    }
}

// ---------------------------------------------------------------------------
// 2. Autoload the helper functions
// ---------------------------------------------------------------------------
$composer = json_decode(file_get_contents('composer.json'), true);
$files = $composer['autoload']['files'] ?? [];

if (! in_array('app/Support/helpers.php', $files, true)) {
    $files[] = 'app/Support/helpers.php';
    $composer['autoload']['files'] = $files;

    file_put_contents(
        'composer.json',
        json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
    );
    step('Added helpers.php to the autoloader');
} else {
    step('Helpers already autoloaded');
}

// ---------------------------------------------------------------------------
// 3. Wire Tailwind 4 into Vite
// ---------------------------------------------------------------------------
$vite = 'vite.config.js';

if (is_file($vite)) {
    $contents = file_get_contents($vite);

    if (! str_contains($contents, '@tailwindcss/vite')) {
        $contents = "import tailwindcss from '@tailwindcss/vite';\n".$contents;
        $contents = preg_replace('/plugins:\s*\[/', "plugins: [\n        tailwindcss(),", $contents, 1);
        file_put_contents($vite, $contents);
        step('Wired Tailwind into Vite');
    } else {
        step('Tailwind already wired into Vite');
    }
}

// ---------------------------------------------------------------------------
// 4. Environment
// ---------------------------------------------------------------------------
/**
 * Set a key in .env, replacing it in place if it already exists.
 *
 * Appending a second copy of a key does NOT work: Laravel loads .env
 * immutably, so the FIRST occurrence wins and the later one is silently
 * ignored. That failure is invisible until something behaves oddly hours
 * later, so every value is merged rather than appended.
 */
function env_set(string $env, string $key, string $value): string
{
    $line = "{$key}={$value}";

    if (preg_match("/^{$key}=.*$/m", $env)) {
        return preg_replace("/^{$key}=.*$/m", $line, $env, 1);
    }

    return rtrim($env, "\n")."\n".$line."\n";
}

if (is_file('.env.westbridge.example')) {
    $env = file_get_contents('.env');
    $added = 0;

    foreach (file('.env.westbridge.example', FILE_IGNORE_NEW_LINES) as $line) {
        $line = trim($line);

        // Keep comments and blank lines out of the merge - they are guidance
        // for whoever reads the example file, not values.
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $env = env_set($env, trim($key), $value);
        $added++;
    }

    file_put_contents('.env', $env);
    unlink('.env.westbridge.example');
    step("Merged {$added} WestBridge settings into .env");
}

if ($useSqlite) {
    // Local development on SQLite: no database server to install or run.
    // Production uses MySQL - the schema is written to work on both.
    $env = file_get_contents('.env');
    $env = env_set($env, 'DB_CONNECTION', 'sqlite');

    // Local defaults that differ from the production block:
    // the dev server's own URL, and mail written to the log file so form
    // submissions can be inspected without an SMTP account.
    $env = env_set($env, 'APP_URL', 'http://127.0.0.1:8000');
    $env = env_set($env, 'MAIL_MAILER', 'log');

    // Comment out the MySQL-only keys so they cannot confuse anyone later.
    foreach (['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
        $env = preg_replace("/^{$key}=(.*)$/m", "# {$key}=\$1   # MySQL only", $env);
    }

    file_put_contents('.env', $env);

    if (! is_dir('database')) {
        mkdir('database', 0755, true);
    }

    if (! is_file('database/database.sqlite')) {
        touch('database/database.sqlite');
    }

    step('Configured SQLite (database/database.sqlite)');
}

echo "\n  Wiring complete.\n";
