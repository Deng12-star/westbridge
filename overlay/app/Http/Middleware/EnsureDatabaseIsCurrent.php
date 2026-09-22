<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * On your own PC only (APP_ENV=local).
 *
 * When new code arrives with new database tables, the site cannot work until
 * RUN-ME.bat has updated the database. Instead of a crash page, this shows a
 * plain message saying exactly that. The live server never needs it:
 * deploy.sh migrates before the site comes back up.
 */
class EnsureDatabaseIsCurrent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local') || app()->runningUnitTests()) {
            return $next($request);
        }

        try {
            $files = count(glob(database_path('migrations/*.php')) ?: []);
            $ran = Schema::hasTable('migrations') ? DB::table('migrations')->count() : 0;
        } catch (Throwable) {
            return $next($request);
        }

        if ($files > $ran) {
            return response($this->page($files - $ran), 503, ['Retry-After' => '60']);
        }

        return $next($request);
    }

    private function page(int $pending): string
    {
        $count = $pending === 1 ? '1 database update is' : $pending.' database updates are';

        return <<<HTML
            <!DOCTYPE html>
            <html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Update needed - WestBridge</title>
            <style>
              body{margin:0;min-height:100vh;display:grid;place-items:center;background:#101829;color:#fff;font-family:system-ui,Segoe UI,sans-serif}
              .box{max-width:520px;margin:24px;padding:40px;background:#fff;color:#1C2B4F;border-radius:6px;border-top:6px solid #89C726}
              h1{margin:0 0 12px;font-size:1.5rem} p{line-height:1.6;color:#44506a} ol{line-height:1.9;color:#1C2B4F;font-weight:600;padding-left:1.2em}
              code{background:#F4FAE9;padding:2px 6px;border-radius:3px}
            </style></head>
            <body><div class="box">
              <h1>The website was updated</h1>
              <p>{$count} waiting. The site will work again as soon as they are applied:</p>
              <ol>
                <li>Close the black server window.</li>
                <li>Double-click <code>RUN-ME.bat</code>.</li>
              </ol>
              <p>Your content is safe - updates only add to the database.</p>
            </div></body></html>
            HTML;
    }
}
