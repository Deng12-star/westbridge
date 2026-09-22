<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogueController;
use App\Http\Controllers\Api\V1\ProductAdminController;
use App\Http\Middleware\EnsureActiveApiUser;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 - /api/v1
|--------------------------------------------------------------------------
| Read endpoints are public and return only what the website already shows.
| Write endpoints need a staff token (Laravel Sanctum) and the matching
| permission. See docs/API.md.
*/

Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function (): void {
    Route::get('site', [CatalogueController::class, 'site'])->name('site');
    Route::get('categories', [CatalogueController::class, 'categories'])->name('categories');
    Route::get('products', [CatalogueController::class, 'products'])->name('products.index');
    Route::get('products/{slug}', [CatalogueController::class, 'product'])->name('products.show');
    Route::get('projects', [CatalogueController::class, 'projects'])->name('projects.index');
    Route::get('projects/{slug}', [CatalogueController::class, 'project'])->name('projects.show');

    // Token endpoints exist only once Sanctum is installed (the launcher
    // installs it). Without it the public endpoints above still work.
    if (class_exists(\Laravel\Sanctum\Sanctum::class)) {
        Route::post('auth/token', [AuthController::class, 'token'])->middleware('throttle:api-token')->name('auth.token');

        Route::middleware(['auth:sanctum', EnsureActiveApiUser::class])->group(function (): void {
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::delete('auth/token', [AuthController::class, 'revoke'])->name('auth.revoke');

            Route::post('products', [ProductAdminController::class, 'store'])->name('products.store');
            Route::match(['put', 'patch'], 'products/{product:id}', [ProductAdminController::class, 'update'])->name('products.update');
            Route::delete('products/{product:id}', [ProductAdminController::class, 'destroy'])->name('products.destroy');
        });
    }
});
