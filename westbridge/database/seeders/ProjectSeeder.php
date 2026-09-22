<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use Database\Seeders\Concerns\SeedsOnce;
use Illuminate\Database\Seeder;

/**
 * Moves the portfolio from config/portfolio.php into the database ONCE.
 * After that the admin panel owns it: deleted projects stay deleted.
 */
class ProjectSeeder extends Seeder
{
    use SeedsOnce;

    public function run(): void
    {
        $this->seedOnce('projects_seeded', Project::class, fn () => $this->seed());
    }

    private function seed(): void
    {
        foreach (config('portfolio', []) as $i => $p) {
            if (! is_array($p) || blank($p['slug'] ?? null)) {
                continue;
            }

            Project::query()->firstOrCreate(
                ['slug' => $p['slug']],
                [
                    'title' => $p['title'],
                    'client' => $p['client'] ?? null,
                    'location' => $p['location'] ?? null,
                    'industry' => $p['industry'] ?? null,
                    'category' => $p['category'] ?? null,
                    'service' => $p['service'] ?? null,
                    'status' => $p['status'] ?? null,
                    'year' => $p['year'] ?? null,
                    'mock' => $p['mock'] ?? 'dashboard',
                    'live_url' => $p['live_url'] ?? null,
                    'show_live_link' => (bool) ($p['show_live_link'] ?? false),
                    'summary' => $p['summary'] ?? null,
                    'overview' => $p['overview'] ?? [],
                    'scope' => $p['scope'] ?? [],
                    'is_published' => true,
                    'sort_order' => $i + 1,
                ],
            );
        }
    }
}
