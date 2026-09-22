<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const MAX_ATTEMPTS_PER_IP = 20;

    public function show(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:190'],
        ]);

        // Five tries per minute per email and address, then a lock-out.
        $key = 'admin-login:'.Str::lower($credentials['email']).'|'.$request->ip();

        // Second limit per address across ALL emails, so one attacker cannot
        // try one password against many accounts.
        $ipKey = 'admin-login-ip:'.$request->ip();

        if (RateLimiter::tooManyAttempts($ipKey, self::MAX_ATTEMPTS_PER_IP)) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts from this connection. Try again in '.RateLimiter::availableIn($ipKey).' seconds.',
            ]);
        }

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Try again in '.RateLimiter::availableIn($key).' seconds.',
            ]);
        }

        // No "remember me": a long-lived sign-in cookie would bypass the
        // inactivity lock and sign-out, so admin sessions never outlive them.
        if (! Auth::attempt([...$credentials, 'is_active' => true], false)) {
            RateLimiter::hit($key, 60);
            RateLimiter::hit($ipKey, 900);

            // Failed sign-ins are logged (never the password) so an attack is visible.
            Log::warning('Admin sign-in failed', ['email' => Str::lower($credentials['email']), 'ip' => $request->ip()]);

            throw ValidationException::withMessages([
                'email' => 'Those details do not match an active staff account.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $request->session()->put(AdminSession::LAST_ACTIVITY, now()->getTimestamp());
        $request->session()->forget([AdminSession::LOCKED, AdminSession::UNLOCK_ATTEMPTS]);

        $request->user()->forceFill(['last_login_at' => now()])->save();
        Log::info('Admin signed in', ['user_id' => $request->user()->id, 'ip' => $request->ip()]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear-Site-Data tells the browser to drop every cached admin page,
        // so the Back button cannot show one after signing out.
        return redirect()->route('login')
            ->with('status', 'You have been signed out.')
            ->header('Clear-Site-Data', '"cache"');
    }
}
