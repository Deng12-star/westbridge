<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Environment check
|--------------------------------------------------------------------------
| Shared by RUN-ME.bat, install.ps1 and install.sh so all three agree on what
| counts as a working machine. Exits 0 when the PC can run the application,
| 1 when something must be fixed first.
|
|     php scripts/check-env.php [--sqlite]
*/

$wantsSqlite = in_array('--sqlite', $argv, true);

// Laravel 12's own floor. The original brief said 8.3+, which is a fine
// target for the production server but is not required to run the code.
const MINIMUM_PHP = '8.2.0';

/** Laravel 12's documented server requirements. */
const REQUIRED_EXTENSIONS = [
    'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash',
    'mbstring', 'openssl', 'pcre', 'pdo', 'session', 'tokenizer', 'xml',
];

/** Not required to boot, but you will hit them sooner or later. */
const RECOMMENDED_EXTENSIONS = [
    'zip' => 'Composer falls back to a slower download method without it',
    'gd' => 'needed for image resizing from Phase 4',
    'intl' => 'needed for correct number and date formatting',
];

$problems = [];
$warnings = [];

echo PHP_EOL.'  Checking this PC'.PHP_EOL.PHP_EOL;

// --- PHP version -----------------------------------------------------------
$phpOk = version_compare(PHP_VERSION, MINIMUM_PHP, '>=');

printf("  %s PHP %s%s", $phpOk ? '[OK]' : '[X] ', PHP_VERSION, PHP_EOL);

if (! $phpOk) {
    $problems[] = sprintf(
        "PHP %s or newer is required. This PC has %s.\n".
        "      Install Laravel Herd (free) to get a current PHP:\n".
        '      https://herd.laravel.com/windows',
        MINIMUM_PHP,
        PHP_VERSION
    );
}

echo '       '.PHP_BINARY.PHP_EOL;

// --- Required extensions ---------------------------------------------------
$missing = array_values(array_filter(
    REQUIRED_EXTENSIONS,
    static fn (string $extension): bool => ! extension_loaded($extension)
));

if ($missing === []) {
    echo '  [OK] All required PHP extensions present'.PHP_EOL;
} else {
    echo '  [X]  Missing PHP extensions: '.implode(', ', $missing).PHP_EOL;
    $problems[] = sprintf(
        "These PHP extensions are switched off: %s\n".
        "      Open your php.ini and remove the ; in front of the matching\n".
        "      extension= lines, then run this again. php.ini is at:\n".
        '      %s',
        implode(', ', $missing),
        php_ini_loaded_file() ?: 'unknown - run: php --ini'
    );
}

// --- The database driver we actually use -----------------------------------
if ($wantsSqlite) {
    if (extension_loaded('pdo_sqlite')) {
        echo '  [OK] SQLite support'.PHP_EOL;
    } else {
        echo '  [X]  SQLite support missing'.PHP_EOL;
        $problems[] = sprintf(
            "The pdo_sqlite extension is switched off, and this setup uses\n".
            "      SQLite so you do not need a database server.\n".
            "      Remove the ; in front of extension=pdo_sqlite in:\n".
            '      %s',
            php_ini_loaded_file() ?: 'your php.ini - run: php --ini'
        );
    }
}

// --- Recommended -----------------------------------------------------------
foreach (RECOMMENDED_EXTENSIONS as $extension => $why) {
    if (! extension_loaded($extension)) {
        $warnings[] = "{$extension} is off - {$why}";
    }
}

foreach ($warnings as $warning) {
    echo '  [ ]  '.$warning.PHP_EOL;
}

// --- The installer itself ---------------------------------------------------
// Windows PowerShell reads .ps1 files as ANSI. A single non-ASCII byte becomes
// mojibake and breaks the parser with errors that point nowhere near the cause,
// so it is checked here rather than discovered the hard way.
$installer = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'install.ps1';

if (is_file($installer)) {
    $source = file_get_contents($installer);

    if (preg_match('/[^\x00-\x7F]/', $source)) {
        echo '  [X]  install.ps1 contains non-ASCII characters'.PHP_EOL;
        $problems[] =
            "install.ps1 contains characters Windows PowerShell cannot read.\n".
            "      It will fail with confusing parser errors. Ask for a\n".
            '      corrected copy rather than trying to run it.';
    } else {
        echo '  [OK] Installer encoding'.PHP_EOL;
    }
}

// --- Verdict ---------------------------------------------------------------
echo PHP_EOL;

if ($problems !== []) {
    echo '  Fix this before continuing:'.PHP_EOL.PHP_EOL;

    foreach ($problems as $index => $problem) {
        echo '  '.($index + 1).'. '.$problem.PHP_EOL.PHP_EOL;
    }

    exit(1);
}

echo '  This PC is ready.'.PHP_EOL.PHP_EOL;
exit(0);
