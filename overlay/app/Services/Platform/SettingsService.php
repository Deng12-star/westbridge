<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Cached settings repository.
 *
 * The whole table is loaded once per request and cached forever; saving any
 * setting flushes it. Settings are read on every page render, so this must
 * never become a per-key query.
 */
class SettingsService
{
    private const CACHE_KEY = 'westbridge.settings';

    private ?Collection $cached = null;

    public function all(): Collection
    {
        if ($this->cached !== null) {
            return $this->cached;
        }

        try {
            return $this->cached = $this->remember();
        } catch (\Throwable) {
            // An unreadable cache entry (e.g. copied from another database
            // engine) must never take the site down: drop it and rebuild.
            try {
                Cache::forget(self::CACHE_KEY);
            } catch (\Throwable) {
                // cache store itself unavailable - fall through to the database
            }

            return $this->cached = $this->load();
        }
    }

    private function remember(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): Collection => $this->load());
    }

    private function load(): Collection
    {
        return (function (): Collection {
            // Table may not exist yet during the very first migration run.
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return collect();
            }

            return Setting::query()
                ->get()
                ->mapWithKeys(fn (Setting $s): array => ["{$s->group}.{$s->key}" => $s->value]);
        })();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()->get($key, $default);

        // Treat an unconfigured setting as absent so templates can hide the element.
        return $value === '' || $value === null ? $default : $value;
    }

    public function group(string $group): Collection
    {
        return $this->all()
            ->filter(fn ($v, $k): bool => str_starts_with($k, $group.'.'))
            ->mapWithKeys(fn ($v, $k): array => [substr($k, strlen($group) + 1) => $v]);
    }

    public function set(string $key, mixed $value): void
    {
        [$group, $name] = explode('.', $key, 2);

        Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $name],
            ['value' => $value],
        );

        $this->flush();
    }

    public function flush(): void
    {
        $this->cached = null;
        Cache::forget(self::CACHE_KEY);
    }
}
