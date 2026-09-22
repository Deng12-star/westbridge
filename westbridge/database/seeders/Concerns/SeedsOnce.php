<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;

/**
 * Starter content is seeded ONCE. After that the admin panel owns it:
 * something deleted from the admin stays deleted, however many times the
 * seeders run again (RUN-ME.bat and deploy.sh run them on every update).
 *
 * The marker is a row in the settings table (group "system"), which the admin
 * Settings screen does not show.
 */
trait SeedsOnce
{
    /**
     * @param  class-string<Model>  $model
     */
    protected function seedOnce(string $marker, string $model, callable $seed): void
    {
        $done = Setting::query()->where('group', 'system')->where('key', $marker)->exists();

        // An installation that already has rows (seeded before this marker
        // existed) is treated as done too - never top it back up.
        if (! $done && ! $model::query()->exists()) {
            $seed();
        }

        if (! $done) {
            Setting::query()->create([
                'group' => 'system',
                'key' => $marker,
                'value' => now()->toAtomString(),
                'type' => 'string',
                'label' => 'Starter content seeded',
                'is_public' => false,
            ]);
        }
    }
}
