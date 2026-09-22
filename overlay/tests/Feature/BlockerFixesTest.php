<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LocalAdminSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed([RoleSeeder::class, SettingSeeder::class]);
});

function superAdmin(): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Super Admin');

    return $user;
}

/* Blocker 1 - no known-password account can reach a server ---------------- */

it('creates no user accounts from the main seeder', function (): void {
    seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBe(0);
});

it('refuses to create the local admin outside APP_ENV=local', function (): void {
    expect(fn () => (new LocalAdminSeeder)->run())->toThrow(RuntimeException::class);
    expect(User::query()->where('email', LocalAdminSeeder::EMAIL)->exists())->toBeFalse();
});

it('fails preflight when the site is not configured for production', function (): void {
    User::factory()->create(['email' => LocalAdminSeeder::EMAIL]);

    artisan('wb:preflight')
        ->expectsOutputToContain('APP_ENV must be "production"')
        ->expectsOutputToContain(LocalAdminSeeder::EMAIL)
        ->assertFailed();
});

/* Blocker 2 - uploads cannot become web pages or scripts ------------------- */

it('stores a disguised upload as a clean image with a safe extension', function (): void {
    if (! function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD is not enabled in this PHP.');
    }

    $jpeg = tempnam(sys_get_temp_dir(), 'wb');
    imagejpeg(imagecreatetruecolor(40, 40), $jpeg);
    file_put_contents($jpeg, '<script>alert(1)</script>', FILE_APPEND);
    $upload = new UploadedFile($jpeg, 'evil.html', 'image/jpeg', null, true);

    actingAs(superAdmin())->post('/admin/products', [
        'name' => 'Disguised', 'currency' => 'USD', 'stock_status' => 'in_stock',
        'is_published' => '1', 'image' => $upload,
    ])->assertRedirect();

    // Either outcome is safe: rejected outright, or stored as a clean .jpg.
    $product = Product::query()->first();
    if ($product === null) {
        expect(session('errors')?->has('image'))->toBeTrue();

        return;
    }

    $path = $product->image_path;
    expect($path)->toEndWith('.jpg')->not->toContain('html');

    $stored = public_path('uploads/'.$path);
    expect(file_get_contents($stored))->not->toContain('<script>');
    @unlink($stored);
});

it('rejects a file that is not really an image', function (): void {
    $fake = tempnam(sys_get_temp_dir(), 'wb');
    file_put_contents($fake, '<?php echo "hi";');
    $upload = new UploadedFile($fake, 'photo.jpg', 'image/jpeg', null, true);

    actingAs(superAdmin())->post('/admin/products', [
        'name' => 'Fake', 'currency' => 'USD', 'stock_status' => 'in_stock', 'image' => $upload,
    ])->assertSessionHasErrors('image');

    expect(Product::query()->count())->toBe(0);
});

/* Blocker 3 - deactivated staff lose API access immediately ---------------- */

it('rejects API writes from a deactivated account that still holds a token', function (): void {
    if (! class_exists(\Laravel\Sanctum\Sanctum::class)) {
        $this->markTestSkipped('Sanctum is not installed.');
    }

    $user = superAdmin();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/products', ['name' => 'Before'])->assertCreated();

    $user->update(['is_active' => false]);
    app('auth')->forgetGuards();

    $this->withToken($token)->postJson('/api/v1/products', ['name' => 'After'])->assertForbidden();
    expect(Product::query()->where('name', 'After')->exists())->toBeFalse();
});

it('revokes API tokens when an admin deactivates someone', function (): void {
    if (! class_exists(\Laravel\Sanctum\Sanctum::class)) {
        $this->markTestSkipped('Sanctum is not installed.');
    }

    $admin = superAdmin();
    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole('Sales');
    $staff->createToken('phone');

    actingAs($admin)->put('/admin/users/'.$staff->id, [
        'name' => $staff->name, 'email' => $staff->email, 'role' => 'Sales', 'is_active' => '0',
    ])->assertRedirect();

    expect($staff->tokens()->count())->toBe(0);
});

/* Blocker 4 - queued email is actually processed ---------------------------- */

it('schedules the queue worker and the backups', function (): void {
    $commands = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
        ->map(fn ($e) => $e->command)->join("\n");

    expect($commands)->toContain('queue:work')->toContain('wb:backup');
});

/* Blocker 5 - backups are written and verified ------------------------------ */

it('backs up an SQLite database and keeps only the newest copies', function (): void {
    $db = tempnam(sys_get_temp_dir(), 'wbdb');
    (new PDO('sqlite:'.$db))->exec('create table t (id integer)');
    config(['database.connections.backuptest' => ['driver' => 'sqlite', 'database' => $db], 'database.default' => 'backuptest']);

    $dir = storage_path('app/backups');
    foreach (range(1, 3) as $i) {
        artisan('wb:backup', ['--label' => 'unittest', '--keep' => 2])->assertSuccessful();
        sleep(1);
    }

    $files = glob($dir.'/unittest-*.sqlite.gz');
    expect($files)->toHaveCount(2);
    expect(substr(gzdecode(file_get_contents($files[0])), 0, 15))->toBe('SQLite format 3');

    array_map('unlink', $files);
    config(['database.default' => 'sqlite']);
});

/* Blocker 7 - deleted starter content stays deleted -------------------------- */

it('never brings back a category deleted in the admin', function (): void {
    seed(CatalogueSeeder::class);
    ProductCategory::query()->where('slug', 'phones')->delete();

    seed(CatalogueSeeder::class);

    expect(ProductCategory::query()->where('slug', 'phones')->exists())->toBeFalse();
});
