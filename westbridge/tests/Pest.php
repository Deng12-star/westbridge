<?php

declare(strict_types=1);

/*
| Feature and unit tests boot the full Laravel application. Each feature test file
| declares RefreshDatabase itself, so the database starts empty every test.
| Tests\TestCase refuses to run against anything but a throwaway database.
*/
uses(Tests\TestCase::class)->in('Feature', 'Unit');
