<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/** Lock screen, unlock, and the keep-alive / status calls the admin page makes. */
class SessionController extends Controller
{
    public function lockScreen(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get(AdminSession::LOCKED)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.lock', ['user' => $request->user()]);
    }

    /** "Lock screen" from the account menu. */
    public function lockNow(Request $request): RedirectResponse|JsonResponse
    {
        $request->session()->put(AdminSession::LOCKED, true);

        return $request->expectsJson()
            ? response()->json(['locked' => true])
            : redirect()->route('admin.lock');
    }

    public function unlock(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['password' => ['required', 'string', 'max:190']]);
        $session = $request->session();

        if (! Hash::check($data['password'], $request->user()->password)) {
            $attempts = (int) $session->get(AdminSession::UNLOCK_ATTEMPTS, 0) + 1;
            $session->put(AdminSession::UNLOCK_ATTEMPTS, $attempts);
            Log::warning('Admin unlock failed', ['user_id' => $request->user()->id, 'ip' => $request->ip(), 'attempt' => $attempts]);

            // Too many wrong passwords on a locked screen: sign out entirely.
            if ($attempts >= AdminSession::MAX_UNLOCK_ATTEMPTS) {
                Auth::guard('web')->logout();
                $session->invalidate();
                $session->regenerateToken();

                return $request->expectsJson()
                    ? response()->json(['authenticated' => false, 'message' => 'Too many attempts. Signed out.'], 401)
                    : redirect()->route('login')->with('status', 'Too many wrong passwords on the locked screen - you have been signed out.');
            }

            $left = AdminSession::MAX_UNLOCK_ATTEMPTS - $attempts;
            $message = 'That password is not correct. '.$left.' '.($left === 1 ? 'try' : 'tries').' left before you are signed out.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'errors' => ['password' => [$message]]], 422);
            }

            throw ValidationException::withMessages(['password' => $message]);
        }

        $session->forget([AdminSession::LOCKED, AdminSession::UNLOCK_ATTEMPTS]);
        $session->put(AdminSession::LAST_ACTIVITY, now()->getTimestamp());
        $session->regenerate();

        return $request->expectsJson()
            ? response()->json(['locked' => false, 'csrf' => csrf_token()])
            : redirect()->intended(route('admin.dashboard'));
    }

    /** Sent by the page while the person is actively using it. */
    public function ping(): JsonResponse
    {
        return response()->json(['ok' => true]);
    }

    /** Polled by the page; does not count as activity. */
    public function status(Request $request): JsonResponse
    {
        $idle = now()->getTimestamp() - (int) $request->session()->get(AdminSession::LAST_ACTIVITY, now()->getTimestamp());

        return response()->json([
            'authenticated' => true,
            'locked' => (bool) $request->session()->get(AdminSession::LOCKED),
            'lock_in' => max(0, AdminSession::lockSeconds() - $idle),
            'logout_in' => max(0, AdminSession::logoutSeconds() - $idle),
        ]);
    }
}
