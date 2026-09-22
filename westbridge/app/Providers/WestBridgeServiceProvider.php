<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\LeadCaptured;
use App\Listeners\NotifyTeamOfLead;
use App\Models\ProductCategory;
use App\Services\Platform\SettingsService;
use App\Services\Seo\SeoService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class WestBridgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(SeoService::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            // Every generated link uses APP_URL, never the request's Host.
            URL::forceRootUrl(config('app.url'));
        }

        if (($proxies = config('westbridge.trusted_proxies')) && method_exists(\Illuminate\Http\Middleware\TrustProxies::class, 'at')) {
            \Illuminate\Http\Middleware\TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        // Every email needs a From address. When MAIL_FROM_ADDRESS is not set
        // in .env, use the contact email from Admin > Settings, or a
        // no-reply address on the site's own domain.
        if (blank(config('mail.from.address')) || config('mail.from.address') === 'hello@example.com') {
            $this->app->booted(function (): void {
                try {
                    $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
                    $from = setting('contact.email') ?: 'no-reply@'.(str_contains($host, '.') ? $host : 'westbridge.local');
                    config([
                        'mail.from.address' => $from,
                        'mail.from.name' => config('mail.from.name') ?: setting('company.name', 'WestBridge Technologies'),
                    ]);
                } catch (\Throwable) {
                    // settings table not ready yet (first install) - leave mail config alone
                }
            });
        }

        Paginator::defaultView('pagination.default');

        // wire:navigate shows a progress bar when a page takes a moment to
        // arrive. Livewire's default is blue; this is the brand lime.
        config(['livewire.navigate.progress_bar_color' => '#89C726']);

        Event::listen(LeadCaptured::class, NotifyTeamOfLead::class);

        // Super Admin passes every permission check, even one added later
        // that the role has not been synced to yet.
        Gate::before(fn ($user) => method_exists($user, 'hasRole') && $user->hasRole('Super Admin') ? true : null);

        // Public API: 60 requests a minute per client; sign-in attempts 5.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('api-token', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        // Navigation data is shared with the header and drawer rather than
        // rebuilt per page. Shop categories come from Admin > Categories.
        View::composer(
            ['components.layout.header', 'components.layout.mobile-drawer'],
            function ($view): void {
                $view->with([
                    'solutions' => config('westbridge.navigation.solutions'),
                    'shopCategories' => $this->shopCategories(),
                    // The News link appears with the first published post.
                    'showInsights' => Schema::hasTable('posts') && \App\Models\Post::query()->live()->exists(),
                ]);
            }
        );
    }

    /** @return array<int, array{title: string, url: string, count: int|null}> */
    private function shopCategories(): array
    {
        if (! Schema::hasTable('product_categories')) {
            return config('westbridge.navigation.shop_categories');
        }

        return ProductCategory::query()->visible()->ordered()
            ->withCount(['products' => fn ($q) => $q->where('is_published', true)])
            ->get()
            ->map(fn (ProductCategory $c) => [
                'title' => $c->name,
                'url' => route('shop.category', $c->slug),
                'count' => $c->products_count ?: null,
            ])
            ->all();
    }
}
