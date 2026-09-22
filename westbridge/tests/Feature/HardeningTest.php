<?php

declare(strict_types=1);

use App\Livewire\ContactForm;
use App\Models\ContactMessage;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ContactMessageReceived;
use App\Support\SafeHtml;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed([RoleSeeder::class, SettingSeeder::class]);
});

function owner(): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Super Admin');

    return $user;
}

/* S3 - login limit per connection, across all emails ---------------------- */

it('blocks a connection that tries many different accounts', function (): void {
    foreach (range(1, 20) as $i) {
        post('/admin/login', ['email' => "user{$i}@example.com", 'password' => 'wrong']);
    }

    $user = owner();
    post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('from this connection');
});

/* S5 - every site route is cacheable (no closures) ------------------------- */

it('defines site routes with controllers, not closures', function (): void {
    $closures = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r) => ($r->getAction('uses') ?? null) instanceof Closure)
        // Laravel's own unnamed health check and local file route are not ours.
        ->filter(fn ($r) => $r->getName() !== null && ! str_starts_with($r->getName(), 'storage.'))
        ->map(fn ($r) => $r->uri())
        ->values()
        ->all();

    expect($closures)->toBe([]);
});

/* S6 - page text cannot carry scripts --------------------------------------- */

it('strips scripts from page text, including slash-separated attributes', function (string $input): void {
    $out = (string) SafeHtml::clean($input);

    expect(strtolower($out))
        ->not->toContain('javascript:')
        ->not->toContain('onmouseover')
        ->not->toContain('onclick')
        ->not->toContain('<script');
})->with([
    '<p/onmouseover="alert(1)">hover</p>',
    '<a/href="javascript:alert(1)">x</a>',
    '<a href="javascript:alert(1)" onclick=alert(2)>y</a>',
    '<a href="jav&#x09;ascript:alert(1)">z</a>',
    '<script>alert(1)</script><p>ok</p>',
]);

it('keeps normal formatting and safe links', function (): void {
    $out = (string) SafeHtml::clean('<p>Hello <strong>there</strong> <a href="https://example.com">site</a></p>');

    expect($out)->toContain('<strong>there</strong>')->toContain('href="https://example.com"');
});

/* S7 - the automatic reply cannot be used to flood an inbox ----------------- */

it('sends at most one automatic reply per address per day', function (): void {
    Notification::fake();

    foreach ([1, 2] as $n) {
        Livewire::test(ContactForm::class)
            ->set('loadedAt', time() - 30)
            ->set('name', 'Someone Else')
            ->set('email', 'victim@example.com')
            ->set('message', "Message number {$n} about office networking.")
            ->call('submit')
            ->assertHasNoErrors();
    }

    expect(ContactMessage::query()->count())->toBe(2);
    Notification::assertSentOnDemandTimes(ContactMessageReceived::class, 1);
});

/* S8 - the map setting only accepts a Google Maps embed --------------------- */

it('rejects a map address that is not a Google Maps embed', function (): void {
    actingAs(owner())->put('/admin/settings/contact', ['values' => ['map_embed' => 'javascript:alert(1)']])
        ->assertSessionHasErrors('values.map_embed');
});

it('accepts a pasted Google Maps iframe and shows only its address', function (): void {
    actingAs(owner())->put('/admin/settings/contact', ['values' => [
        'map_embed' => '<iframe src="https://www.google.com/maps/embed?pb=abc" width="600"></iframe>',
    ]])->assertSessionHasNoErrors();

    get('/contact')->assertOk()->assertSee('src="https://www.google.com/maps/embed?pb=abc"', false);
});

/* S10 - deleted products and messages can be restored ---------------------- */

it('moves a deleted product to Recently deleted and restores it', function (): void {
    $product = Product::query()->create(['name' => 'Router', 'slug' => 'router', 'currency' => 'USD', 'stock_status' => 'in_stock', 'is_published' => true]);
    $admin = owner();

    actingAs($admin)->delete('/admin/products/'.$product->id)->assertRedirect();
    get('/product/router')->assertNotFound();
    expect(Product::onlyTrashed()->count())->toBe(1);

    actingAs($admin)->get('/admin/products?status=deleted')->assertOk()->assertSeeText('Router');
    actingAs($admin)->patch('/admin/products/'.$product->id.'/restore')->assertRedirect();

    get('/product/router')->assertOk();
});

it('gives a new product a fresh address while a deleted one still holds the old', function (): void {
    Product::query()->create(['name' => 'Router', 'slug' => 'router', 'currency' => 'USD', 'stock_status' => 'in_stock'])->delete();

    actingAs(owner())->post('/admin/products', ['name' => 'Router', 'currency' => 'USD', 'stock_status' => 'in_stock', 'is_published' => '1'])
        ->assertRedirect();

    expect(Product::query()->sole()->slug)->toBe('router-2');
});

it('restores a deleted message together with its lead', function (): void {
    Notification::fake();
    Livewire::test(ContactForm::class)
        ->set('loadedAt', time() - 30)->set('name', 'Ana Lado')->set('email', 'ana@example.com')
        ->set('message', 'Please call me about Starlink installation.')->call('submit');

    $message = ContactMessage::query()->sole();
    $admin = owner();

    actingAs($admin)->delete('/admin/messages/'.$message->id)->assertRedirect();
    expect(ContactMessage::query()->count())->toBe(0)->and(Lead::query()->count())->toBe(0);

    actingAs($admin)->patch('/admin/messages/'.$message->id.'/restore')->assertRedirect();
    expect(ContactMessage::query()->count())->toBe(1)->and(Lead::query()->count())->toBe(1);
});

it('removes items for good 30 days after deletion', function (): void {
    $old = Product::query()->create(['name' => 'Old', 'slug' => 'old', 'currency' => 'USD', 'stock_status' => 'in_stock']);
    $recent = Product::query()->create(['name' => 'Recent', 'slug' => 'recent', 'currency' => 'USD', 'stock_status' => 'in_stock']);
    $old->delete();
    $recent->delete();
    Product::withTrashed()->whereKey($old->id)->update(['deleted_at' => now()->subDays(31)]);

    artisan('model:prune', ['--model' => [Product::class]])->assertSuccessful();

    expect(Product::withTrashed()->whereKey($old->id)->exists())->toBeFalse()
        ->and(Product::withTrashed()->whereKey($recent->id)->exists())->toBeTrue();
});

/* S11 - a message is never saved without its lead --------------------------- */

it('saves nothing if creating the lead fails', function (): void {
    Notification::fake();
    Lead::creating(fn () => throw new RuntimeException('database hiccup'));

    expect(fn () => Livewire::test(ContactForm::class)
        ->set('loadedAt', time() - 30)->set('name', 'Ana Lado')->set('email', 'ana@example.com')
        ->set('message', 'Please call me about Starlink installation.')->call('submit'))
        ->toThrow(RuntimeException::class);

    expect(ContactMessage::query()->count())->toBe(0);
});

/* Blade: a one-line @php(...) before a @php ... @endphp block makes Blade
   swallow everything between them as PHP. Never mix the two in one file. */
it('never mixes one-line and block @php in the same template', function (): void {
    $mixed = collect(\Illuminate\Support\Facades\File::allFiles(resource_path('views')))
        ->filter(fn ($f) => str_contains($f->getContents(), '@php(') && str_contains($f->getContents(), '@endphp'))
        ->map(fn ($f) => $f->getRelativePathname())
        ->values()
        ->all();

    expect($mixed)->toBe([]);
});
