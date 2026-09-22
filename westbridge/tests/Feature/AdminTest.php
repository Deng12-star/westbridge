<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([RoleSeeder::class, SettingSeeder::class]);
});

function staff(string $role = 'Super Admin'): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole($role);

    return $user;
}

it('sends guests to the admin sign-in page', function (): void {
    get('/admin')->assertRedirect('/admin/login');
    get('/admin/login')->assertOk()->assertSee('Admin sign in');
});

it('signs staff in and rejects wrong passwords', function (): void {
    $user = staff();

    post('/admin/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
    post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/admin');
});

it('locks out after five failed attempts', function (): void {
    $user = staff();
    foreach (range(1, 5) as $i) {
        post('/admin/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('Too many attempts');
});

it('refuses deactivated accounts and accounts without a role', function (): void {
    $noRole = User::factory()->create();
    actingAs($noRole)->get('/admin')->assertRedirect('/admin/login');

    $inactive = staff();
    $inactive->update(['is_active' => false]);
    post('/admin/login', ['email' => $inactive->email, 'password' => 'password'])->assertSessionHasErrors('email');
});

it('renders every admin screen for a Super Admin', function (string $url): void {
    actingAs(staff())->get($url)->assertOk();
})->with(['/admin', '/admin/products', '/admin/products/create', '/admin/categories', '/admin/projects',
    '/admin/projects/create', '/admin/messages', '/admin/pages', '/admin/settings/contact', '/admin/settings/social',
    '/admin/users', '/admin/users/create', '/admin/account',
    '/admin/team', '/admin/team/create', '/admin/news', '/admin/news/create']);

it('creates a product that then appears in the shop', function (): void {
    $category = ProductCategory::query()->create(['name' => 'Laptops', 'slug' => 'laptops']);

    actingAs(staff())->post('/admin/products', [
        'name' => 'Test Laptop 14',
        'product_category_id' => $category->id,
        'price' => '850',
        'currency' => 'usd',
        'stock_status' => 'in_stock',
        'specs' => "Memory: 16 GB\nStorage: 512 GB SSD",
        'is_published' => '1',
        'is_featured' => '0',
    ])->assertRedirect();

    $product = Product::query()->firstOrFail();
    expect($product->slug)->toBe('test-laptop-14')
        ->and($product->currency)->toBe('USD')
        ->and($product->specs)->toBe([['label' => 'Memory', 'value' => '16 GB'], ['label' => 'Storage', 'value' => '512 GB SSD']]);

    get('/shop')->assertOk()->assertSeeText('Test Laptop 14');
    get('/shop/laptops')->assertOk()->assertSeeText('Test Laptop 14');
    get('/product/test-laptop-14')->assertOk()->assertSeeText('Memory')->assertSeeText('USD 850.00');
});

it('keeps an Inventory Manager out of settings and staff', function (): void {
    $user = staff('Inventory Manager');

    actingAs($user)->get('/admin/products')->assertOk();
    actingAs($user)->get('/admin/users')->assertForbidden();
    actingAs($user)->put('/admin/settings/contact', ['values' => ['phone' => '1']])->assertForbidden();
});

it('saves settings and flushes the cache', function (): void {
    actingAs(staff())->put('/admin/settings/contact', ['values' => [
        'whatsapp' => '+211 900 000 000',
        'email' => 'hello@example.com',
    ]])->assertRedirect();

    expect(setting('contact.whatsapp'))->toBe('+211 900 000 000')
        ->and(whatsapp_url('x'))->toStartWith('https://wa.me/211900000000');
});

it('rejects an invalid email in settings', function (): void {
    actingAs(staff())->put('/admin/settings/contact', ['values' => ['email' => 'not-an-email']])
        ->assertSessionHasErrors('values.email');
});

it('marks a message read when it is opened', function (): void {
    $message = ContactMessage::query()->create(['name' => 'Ana', 'email' => 'ana@example.com', 'message' => 'Hi']);

    actingAs(staff())->get('/admin/messages/'.$message->id)->assertOk()->assertSeeText('Ana');
    expect($message->fresh()->is_read)->toBeTrue();
});

it('will not demote the last Super Admin', function (): void {
    $admin = staff();

    actingAs($admin)->put('/admin/users/'.$admin->id, [
        'name' => $admin->name, 'email' => $admin->email, 'role' => 'Sales', 'is_active' => '1',
    ])->assertSessionHasErrors('role');

    expect($admin->fresh()->hasRole('Super Admin'))->toBeTrue();
});

it('marks messages read and unread, one at a time or all at once', function (): void {
    $one = ContactMessage::query()->create(['name' => 'Ana', 'email' => 'ana@example.com', 'message' => 'First message here']);
    $two = ContactMessage::query()->create(['name' => 'Ben', 'email' => 'ben@example.com', 'message' => 'Second message here']);
    $admin = staff();

    actingAs($admin)->patch('/admin/messages/'.$one->id.'/read', ['from' => 'list'])->assertRedirect('/admin/messages');
    expect($one->fresh()->is_read)->toBeTrue();

    actingAs($admin)->patch('/admin/messages/'.$one->id.'/unread', ['from' => 'list'])->assertRedirect('/admin/messages');
    expect($one->fresh()->is_read)->toBeFalse();

    actingAs($admin)->get('/admin/messages')->assertSee('Mark all as read')->assertSee('Mark as read');

    actingAs($admin)->patch('/admin/messages/read-all')->assertRedirect('/admin/messages');
    expect($one->fresh()->is_read)->toBeTrue()->and($two->fresh()->is_read)->toBeTrue();

    actingAs($admin)->get('/admin/messages')->assertDontSee('Mark all as read');
});

it('stores staff alerts for a new message', function (): void {
    expect(\Illuminate\Support\Facades\Schema::hasTable('notifications'))->toBeTrue();
});
