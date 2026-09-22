<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The API equivalent of EnsureAdminAccess: a token only works while its
 * owner is an active staff member with a role. Deactivating someone cuts
 * their API access on the very next request - their tokens do not outlive
 * their account.
 */
class EnsureActiveApiUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canUseAdmin()) {
            $user?->currentAccessToken()?->delete();

            return response()->json(['message' => 'This account no longer has access.'], 403);
        }

        return $next($request);
    }
}
