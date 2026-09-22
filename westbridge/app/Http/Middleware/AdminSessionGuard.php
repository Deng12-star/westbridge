<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AdminSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin inactivity protection, enforced on the server:
 *
 *   idle longer than the lock time    -> the screen locks; the password is
 *                                        needed to carry on (nothing is lost)
 *   idle longer than the logout time  -> signed out completely
 *
 * "Activity" is any admin request, plus the keep-alive the page sends while
 * someone is typing or clicking - so writing a long post never counts as idle.
 * The status check the page polls does NOT count as activity.
 */
class AdminSessionGuard
{
    /** Routes that work while locked (the lock screen itself, sign-out, status). */
    private const ALLOWED_WHILE_LOCKED = ['admin.lock', 'admin.unlock', 'admin.logout', 'admin.session.status'];

    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();
        $now = now()->getTimestamp();
        $last = (int) $session->get(AdminSession::LAST_ACTIVITY, $now);
        $idle = $now - $last;

        if ($idle >= AdminSession::logoutSeconds()) {
            return $this->expire($request);
        }

        if ($idle >= AdminSession::lockSeconds()) {
            $session->put(AdminSession::LOCKED, true);
        }

        if ($session->get(AdminSession::LOCKED) && ! $request->routeIs(...self::ALLOWED_WHILE_LOCKED)) {
            if ($request->expectsJson()) {
                return response()->json(['locked' => true, 'message' => 'The screen is locked.'], 423);
            }

            if ($request->isMethod('GET')) {
                $session->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('admin.lock');
        }

        if (! $request->routeIs('admin.session.status') && ! $session->get(AdminSession::LOCKED)) {
            $session->put(AdminSession::LAST_ACTIVITY, $now);
        }

        return $next($request);
    }

    private function expire(Request $request): Response
    {
        Log::info('Admin session expired after inactivity', ['user_id' => $request->user()?->id]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['authenticated' => false, 'message' => 'Signed out after inactivity.'], 401);
        }

        return redirect()->route('login')
            ->with('status', 'You were signed out after '.intdiv(AdminSession::logoutSeconds(), 60).' minutes without activity.');
    }
}
