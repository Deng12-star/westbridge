<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/*
| Sanctum's HasApiTokens, applied only when Sanctum is installed.
|
| The launcher installs laravel/sanctum on its next run. Until then the User
| model must still load, so this trait is declared empty when the real one is
| missing and the API's token endpoints simply are not registered.
*/
if (trait_exists(\Laravel\Sanctum\HasApiTokens::class)) {
    trait HasApiTokensWhenAvailable
    {
        use \Laravel\Sanctum\HasApiTokens;
    }
} else {
    trait HasApiTokensWhenAvailable
    {
    }
}
