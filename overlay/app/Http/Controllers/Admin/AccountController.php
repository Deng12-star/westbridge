<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.account', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(10)],
        ]);

        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;

        $passwordChanged = filled($data['password'] ?? null);
        if ($passwordChanged) {
            $user->password = $data['password'];
        }

        $user->save();

        if ($passwordChanged) {
            // A new password must lock out anyone else using the old one:
            // other browsers are signed out and API tokens revoked.
            Auth::logoutOtherDevices($data['password']);
            $user->revokeApiTokens();
            $request->session()->regenerate();
        }

        return back()->with('status', filled($data['password'] ?? null) ? 'Password changed.' : 'Account saved.');
    }
}
