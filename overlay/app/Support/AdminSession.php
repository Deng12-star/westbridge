<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The two admin inactivity limits, read from Settings > Security and kept
 * within safe bounds whatever is typed there.
 */
final class AdminSession
{
    public const LAST_ACTIVITY = 'admin.last_activity';

    public const LOCKED = 'admin.locked';

    public const UNLOCK_ATTEMPTS = 'admin.unlock_attempts';

    public const MAX_UNLOCK_ATTEMPTS = 5;

    public static function lockSeconds(): int
    {
        return 60 * self::clamp((int) setting('security.admin_lock_minutes', 15), 5, 120);
    }

    public static function logoutSeconds(): int
    {
        // Always at least a minute longer than the lock, so a locked screen
        // can still be unlocked before the session ends.
        $wanted = max(
            60 * self::clamp((int) setting('security.admin_logout_minutes', 60), 10, 480),
            self::lockSeconds() + 60,
        );

        // The server forgets a session after SESSION_LIFETIME minutes anyway;
        // never promise longer than that.
        return min($wanted, max(600, 60 * (int) config('session.lifetime', 120)));
    }

    private static function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }
}
