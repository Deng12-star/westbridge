<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\AdminSession;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\post;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, SettingSeeder::class]);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('Super Admin');

    // Sign in the real way, so the activity clock starts.
    post('/admin/login', ['email' => $this->admin->email, 'password' => 'password'])->assertRedirect('/admin');
});

it('works normally while active', function (): void {
    get('/admin')->assertOk();

    $this->travel(10)->minutes();
    get('/admin/products')->assertOk();

    $this->travel(10)->minutes();          // 10 min since the last request
    get('/admin')->assertOk();
});

it('locks the screen after 15 idle minutes and unlocks with the password', function (): void {
    $this->travel(16)->minutes();

    get('/admin/products')->assertRedirect('/admin/lock');
    get('/admin/lock')->assertOk()->assertSee('Screen locked');

    // Locked means locked: every other screen is refused.
    get('/admin/settings/contact')->assertRedirect('/admin/lock');

    post('/admin/lock', ['password' => 'password'])->assertRedirect('/admin/settings/contact');
    get('/admin/products')->assertOk();
});

it('signs out completely after 60 idle minutes', function (): void {
    $this->travel(61)->minutes();

    get('/admin')->assertRedirect('/admin/login');
    $this->assertGuest();

    get('/admin')->assertRedirect('/admin/login');
});

it('signs out after five wrong passwords on the lock screen', function (): void {
    $this->travel(16)->minutes();
    get('/admin');

    foreach (range(1, 4) as $i) {
        post('/admin/lock', ['password' => 'wrong'])->assertSessionHasErrors('password');
    }
    post('/admin/lock', ['password' => 'wrong'])->assertRedirect('/admin/login');

    $this->assertGuest();
});

it('counts keep-alive pings as activity but not status checks', function (): void {
    $this->travel(10)->minutes();
    postJson('/admin/session/ping')->assertOk();

    $this->travel(10)->minutes();          // 20 min since sign-in, 10 since the ping
    getJson('/admin/session/status')->assertOk()->assertJson(['locked' => false]);

    $this->travel(6)->minutes();           // 16 min since the ping; status did not reset it
    getJson('/admin/session/status')->assertOk()->assertJson(['locked' => true]);
});

it('answers the page in JSON when locked or signed out', function (): void {
    $this->travel(16)->minutes();
    postJson('/admin/session/ping')->assertStatus(423);

    $this->travel(60)->minutes();
    getJson('/admin/session/status')->assertStatus(401);
});

it('can be locked on purpose from the account menu', function (): void {
    post('/admin/lock-now')->assertRedirect('/admin/lock');
    get('/admin')->assertRedirect('/admin/lock');
});

it('uses the limits set in Settings > Security', function (): void {
    setting_service()->set('security.admin_lock_minutes', '5');

    $this->travel(6)->minutes();
    get('/admin')->assertRedirect('/admin/lock');
});

it('keeps unsafe values out of the security settings', function (): void {
    $this->put('/admin/settings/security', ['values' => ['admin_lock_minutes' => '1', 'admin_logout_minutes' => '10']])
        ->assertSessionHasErrors('values.admin_lock_minutes');

    $this->put('/admin/settings/security', ['values' => ['admin_lock_minutes' => '30', 'admin_logout_minutes' => '20']])
        ->assertSessionHasErrors('values.admin_logout_minutes');

    expect(AdminSession::lockSeconds())->toBe(15 * 60);
});

it('never lets the browser keep admin pages, and clears them on sign-out', function (): void {
    $cache = (string) get('/admin')->headers->get('Cache-Control');
    expect($cache)->toContain('no-store')->toContain('no-cache')->toContain('must-revalidate');

    $logout = post('/admin/logout');
    $logout->assertRedirect('/admin/login')->assertHeader('Clear-Site-Data', '"cache"');

    // Back button after sign-out: the page is re-requested and refused.
    get('/admin/products')->assertRedirect('/admin/login');
});

it('no longer offers "keep me signed in"', function (): void {
    post('/admin/logout');
    get('/admin/login')->assertOk()->assertDontSee('Keep me signed in');
});

it('sends a signed-in person back to the dashboard if Back reaches the sign-in page', function (): void {
    $cache = (string) get('/admin/login')->headers->get('Cache-Control');

    get('/admin/login')->assertRedirect('/admin');
    expect($cache)->toContain('no-store');
});

it('puts the Back-button guard on every admin page', function (): void {
    get('/admin')->assertOk()->assertSee('You are still signed in')->assertSee('wbGuard', false);
});
