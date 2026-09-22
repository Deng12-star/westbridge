<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Delivered projects, managed from Admin > Projects.
 *
 * Reads the projects table. config/portfolio.php is only the starter content
 * (seeded once) and the source for a site not yet migrated.
 * Views always receive the same plain-array shape.
 */
class PortfolioRepository
{
    /** @return Collection<int, array<string, mixed>> */
    public function all(): Collection
    {
        return $this->source()
            ->map(fn (array $p) => $this->hydrate($p))
            ->values();
    }

    /** @return array<string, mixed>|null */
    public function find(string $slug): ?array
    {
        return $this->all()->firstWhere('slug', $slug);
    }

    /** @return Collection<int, array<string, mixed>> */
    public function others(string $slug, int $limit = 2): Collection
    {
        return $this->all()->reject(fn ($p) => $p['slug'] === $slug)->take($limit)->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    private function source(): Collection
    {
        // Once the table exists the database is the only source: an empty
        // portfolio means the admin removed everything, and the site must
        // respect that. The config file is read only by a site that has not
        // been migrated yet.
        if (Schema::hasTable('projects')) {
            return Project::query()->published()->ordered()->get()
                ->map(fn (Project $p) => [...$p->toArray(), 'image' => $p->imageUrl()]);
        }

        return collect(config('portfolio', []))
            ->filter(fn ($p) => is_array($p) && filled($p['slug'] ?? null));
    }

    /** @param array<string, mixed> $p */
    private function hydrate(array $p): array
    {
        // An uploaded image wins; otherwise a file dropped at
        // public/portfolio/{slug}.jpg; otherwise the drawn panel.
        $image = $p['image'] ?? null;
        if (! $image) {
            foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
                $relative = 'portfolio/'.$p['slug'].'.'.$ext;
                if (is_file(public_path($relative))) {
                    $image = asset($relative);
                    break;
                }
            }
        }

        $liveUrl = ($p['show_live_link'] ?? false) ? ($p['live_url'] ?? null) : null;

        return array_merge([
            'client' => null,
            'location' => null,
            'industry' => null,
            'category' => null,
            'service' => null,
            'status' => null,
            'year' => null,
            'mock' => 'dashboard',
            'summary' => null,
            'overview' => [],
            'scope' => [],
        ], array_filter($p, fn ($v) => $v !== null), [
            'overview' => array_values(array_filter((array) ($p['overview'] ?? []), 'filled')),
            'scope' => array_values(array_filter((array) ($p['scope'] ?? []), 'filled')),
            'image' => $image,
            'live_url' => $liveUrl,
            'domain' => $liveUrl ? parse_url($liveUrl, PHP_URL_HOST) : null,
        ]);
    }
}
