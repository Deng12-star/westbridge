<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Second safety net (the first is tests/bootstrap.php). Runs before
     * Laravel boots, so before RefreshDatabase can empty anything.
     */
    protected function setUp(): void
    {
        $database = (string) getenv('DB_DATABASE');

        if ($database !== ':memory:' && ! str_ends_with($database, '_test')) {
            throw new RuntimeException("Refusing to run tests against the database '{$database}'.");
        }

        // A cached config ignores the variables above and points at the real
        // database. RUN-TESTS.bat clears it; refuse if it is still there.
        if (is_file(dirname(__DIR__).'/bootstrap/cache/config.php')) {
            throw new RuntimeException('Config is cached - run "php artisan config:clear" before testing.');
        }

        parent::setUp();
    }
}
