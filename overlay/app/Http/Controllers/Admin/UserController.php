<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with('roles')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['is_active' => true]), 'roles' => $this->roles()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $user = User::query()->create([
            ...collect($data)->except('role')->all(),
            'email_verified_at' => now(),
        ]);
        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')->with('status', 'Staff account created for '.$user->name.'.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['user' => $user, 'roles' => $this->roles()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        // Never let the last active Super Admin lock everyone out.
        if ($user->isSuperAdmin() && ($data['role'] !== 'Super Admin' || ! $data['is_active']) && $this->superAdminCount() <= 1) {
            return back()->withErrors(['role' => 'This is the only active Super Admin. Make someone else Super Admin first.']);
        }

        $passwordChanged = filled($data['password'] ?? null);
        if (! $passwordChanged) {
            unset($data['password']);
        }

        $roleChanged = ! $user->hasRole($data['role']);
        $deactivated = $user->is_active && ! $data['is_active'];

        $user->update(collect($data)->except('role')->all());
        $user->syncRoles([$data['role']]);

        // Any change to someone's access ends their existing sign-ins elsewhere.
        if ($passwordChanged || $roleChanged || $deactivated) {
            $user->revokeApiTokens();

            if (! $user->is($request->user())) {
                $this->endSessions($user);
            }
        }

        return redirect()->route('admin.users.index')->with('status', 'Saved.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($user->isSuperAdmin() && $this->superAdminCount() <= 1) {
            return back()->withErrors(['user' => 'This is the only Super Admin.']);
        }

        $user->revokeApiTokens();
        $this->endSessions($user);
        $user->delete();

        return back()->with('status', 'Account deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:32'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'role' => ['required', Rule::in($this->roles())],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(10)],
        ]);

        return [...$data, 'is_active' => $request->boolean('is_active')];
    }

    /**
     * Signs the person out on every browser. With database sessions their
     * rows are removed; their remember-me token is rotated either way.
     */
    private function endSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        $user->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
    }

    /** @return list<string> */
    private function roles(): array
    {
        return Role::query()->orderBy('name')->pluck('name')->all();
    }

    private function superAdminCount(): int
    {
        return User::role('Super Admin')->where('is_active', true)->count();
    }
}
