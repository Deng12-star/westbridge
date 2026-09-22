<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin gate: signed in, account active, and holding at least one staff role.
 * A deactivated account is signed out on its next request, not at next login.
 */
class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['authenticated' => false], 401)
                : redirect()->guest(route('login'));
        }

        if (! $user->canUseAdmin()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This account does not have access to the admin panel.',
            ]);
        }

        return $next($request);
    }
}
