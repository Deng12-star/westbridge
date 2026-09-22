<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\ProductCategory;
use App\Models\TeamMember;
use App\Models\User;
use App\Support\PostFormatter;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SecurityCategoriesSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed([RoleSeeder::class, SettingSeeder::class, PageSeeder::class]);
});

function staffWith(string $role): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole($role);

    return $user;
}

function livePost(array $attributes = []): Post
{
    return Post::query()->create([
        'title' => 'New office opening',
        'slug' => 'new-office-opening',
        'category' => 'News',
        'body' => 'We have opened.',
        'body_html' => '<p>We have opened.</p>',
        'is_published' => true,
        'published_at' => now()->subDay(),
        ...$attributes,
    ]);
}

/* Team ------------------------------------------------------------------------ */

it('hides the team section until someone is added', function (): void {
    get('/about')->assertOk()->assertDontSeeText('The people behind WestBridge');
});

it('shows visible team members on the About page with their position', function (): void {
    TeamMember::query()->create(['name' => 'Jane Akech', 'position' => 'Operations Manager', 'is_published' => true]);
    TeamMember::query()->create(['name' => 'Hidden Person', 'position' => 'Engineer', 'is_published' => false]);

    get('/about')->assertOk()
        ->assertSeeText('The people behind WestBridge')
        ->assertSeeText('Jane Akech')
        ->assertSeeText('Operations Manager')
        ->assertSeeText('JA')
        ->assertDontSeeText('Hidden Person');
});

it('lets a Content Manager add a team member', function (): void {
    actingAs(staffWith('Content Manager'))->post('/admin/team', [
        'name' => 'Peter Deng', 'position' => 'Network Engineer', 'is_published' => '1',
    ])->assertRedirect('/admin/team');

    expect(TeamMember::query()->sole()->position)->toBe('Network Engineer');
});

it('keeps Sales and Inventory staff out of team and news', function (string $role): void {
    $user = staffWith($role);

    actingAs($user)->get('/admin/team')->assertForbidden();
    actingAs($user)->get('/admin/news')->assertForbidden();
})->with(['Sales', 'Inventory Manager']);

it('only accepts a LinkedIn address for LinkedIn', function (): void {
    actingAs(staffWith('Super Admin'))->post('/admin/team', [
        'name' => 'X', 'position' => 'Y', 'linkedin_url' => 'https://evil.example.com/x',
    ])->assertSessionHasErrors('linkedin_url');
});

/* News ------------------------------------------------------------------------ */

it('publishes a post written in the admin', function (): void {
    actingAs(staffWith('Content Manager'))->post('/admin/news', [
        'title' => 'We now install CCTV',
        'category' => 'Announcement',
        'body' => "Big news.\n\n## What we offer\n\n- Cameras\n- **Intercoms**",
        'is_published' => '1',
    ])->assertRedirect();

    $post = Post::query()->sole();
    expect($post->slug)->toBe('we-now-install-cctv')
        ->and($post->body_html)->toContain('<h3>What we offer</h3>')->toContain('<strong>Intercoms</strong>')
        ->and($post->isLive())->toBeTrue();

    get('/news')->assertOk()->assertSeeText('We now install CCTV');
    get('/news/we-now-install-cctv')->assertOk()->assertSee('<h3>What we offer</h3>', false);
});

it('keeps drafts and scheduled posts off the public site', function (): void {
    livePost(['slug' => 'draft-one', 'title' => 'Draft one', 'is_published' => false]);
    livePost(['slug' => 'future-one', 'title' => 'Future one', 'published_at' => now()->addWeek()]);

    get('/news')->assertOk()->assertDontSeeText('Draft one')->assertDontSeeText('Future one');
    get('/news/draft-one')->assertNotFound();
    get('/news/future-one')->assertNotFound();
});

it('shows the News link only once a post is live', function (): void {
    get('/')->assertDontSee('href="'.route('news.index').'"', false);

    livePost();

    get('/')->assertSee('href="'.route('news.index').'"', false);
});

it('strips unsafe markup from a post', function (): void {
    $html = (string) PostFormatter::toHtml('<p onclick="x()">Hi</p><script>alert(1)</script><a href="javascript:alert(1)">l</a>');

    expect(strtolower($html))->not->toContain('onclick')->not->toContain('<script')->not->toContain('javascript:');
});

it('lists live posts in the sitemap and redirects old insights links', function (): void {
    livePost();

    get('/sitemap.xml')->assertOk()->assertSee('/news/new-office-opening', false);
    get('/insights')->assertRedirect('/news');
    get('/insights/new-office-opening')->assertRedirect('/news/new-office-opening');
});

it('publishes article structured data for a post', function (): void {
    livePost();

    $html = get('/news/new-office-opening')->getContent();
    expect($html)->toContain('"@type":"NewsArticle"')->toContain('"headline":"New office opening"');
});

/* Services & CCTV --------------------------------------------------------------- */

it('renders every service page, including CCTV & Intercom', function (string $url, string $text): void {
    get($url)->assertOk()->assertSeeText($text)->assertSeeText('What is included')->assertDontSeeText('scheduled for Phase');
})->with([
    ['/services/software-development', 'Software Development'],
    ['/services/it-networking', 'IT & Networking'],
    ['/services/starlink', 'Starlink Solutions'],
    ['/services/cctv-intercom', 'CCTV & Intercom Systems'],
]);

it('lists CCTV & Intercom in the menu and on the homepage', function (): void {
    get('/')->assertOk()->assertSeeText('CCTV & Intercom');
});

it('adds the CCTV and intercom shop categories exactly once', function (): void {
    seed(CatalogueSeeder::class);
    seed(SecurityCategoriesSeeder::class);

    expect(ProductCategory::query()->whereIn('slug', ['cctv-cameras', 'intercom-systems'])->count())->toBe(2);

    ProductCategory::query()->where('slug', 'intercom-systems')->delete();
    seed(SecurityCategoriesSeeder::class);

    expect(ProductCategory::query()->where('slug', 'intercom-systems')->exists())->toBeFalse();
});
