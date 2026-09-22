<?php

declare(strict_types=1);

/*
| Runs before any test. Forces a throwaway in-memory SQLite database so the
| suite can never touch database/database.sqlite, whatever .env says.
| (A CI run that supplies its own database ending in _test is left alone.)
|
| Laravel reads environment variables that already exist in preference to
| .env, so setting them here wins.
*/
$current = getenv('DB_DATABASE');

if ($current === false || ! str_ends_with((string) $current, '_test')) {
    foreach (['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:'] as $key => $value) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

require __DIR__.'/../vendor/autoload.php';
