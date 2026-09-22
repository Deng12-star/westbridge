<?php

use App\Http\Middleware\EnsureDatabaseIsCurrent;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(SecurityHeaders::class);

        // Local only: a clear "run RUN-ME.bat" page instead of a crash when
        // new code is waiting for a database update.
        $middleware->web(prepend: [EnsureDatabaseIsCurrent::class]);

        // Only answer to the site's own host name (from APP_URL). Stops forged
        // Host headers from ending up in links, the sitemap or the canonical
        // tag. Laravel skips this check locally and in tests.
        $middleware->trustHosts();

        // Staff who are not signed in go to the admin sign-in page; staff who
        // are signed in and open that page go straight to the dashboard.
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));

        // Trusted proxies (TRUSTED_PROXIES in .env) are applied in
        // WestBridgeServiceProvider, where cached config is available.
    })
    ->withSchedule(function (Schedule $schedule) {
        // One cron line on the server - `php artisan schedule:run` every
        // minute - drives everything below. See docs/DEPLOYMENT.md.

        // Sends queued email (new-message alerts, auto-replies). Without this
        // nothing queued is ever delivered.
        $schedule->command('queue:work --stop-when-empty --max-time=50 --tries=3')
            ->everyMinute()
            ->withoutOverlapping(5);

        // Nightly database backup (+ photos on Sundays), kept for 14 days.
        $schedule->command('wb:backup --keep=14')->dailyAt('02:00')->withoutOverlapping();
        $schedule->command('wb:backup --with-uploads --keep=8 --label=weekly')->weeklyOn(0, '02:30')->withoutOverlapping();

        $schedule->command('queue:prune-failed --hours=720')->weekly();

        // Removes products and messages deleted more than 30 days ago.
        $schedule->command('model:prune')->dailyAt('03:30');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // The API always answers in JSON, including its errors.
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());
    })->create();
