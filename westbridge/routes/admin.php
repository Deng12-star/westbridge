<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\AdminSessionGuard;
use App\Http\Middleware\EnsureAdminAccess;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel - /admin
|--------------------------------------------------------------------------
| Every screen is behind EnsureAdminAccess, and every action is checked
| against a permission (module.action, see RoleSeeder). Hiding a button is
| presentation; the `can:` middleware is the control.
*/

Route::prefix('admin')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AuthController::class, 'show'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('admin.login.attempt');
    });

    // AuthenticateSession signs a browser out as soon as the account's
    // password changes anywhere - so a reset really locks the old one out.
    // AdminSessionGuard locks the screen / signs out after inactivity.
    Route::middleware([EnsureAdminAccess::class, AuthenticateSession::class, AdminSessionGuard::class])->name('admin.')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        // Session: lock screen, unlock, keep-alive and status
        Route::get('lock', [SessionController::class, 'lockScreen'])->name('lock');
        Route::post('lock', [SessionController::class, 'unlock'])->middleware('throttle:10,1')->name('unlock');
        Route::post('lock-now', [SessionController::class, 'lockNow'])->name('lock.now');
        Route::post('session/ping', [SessionController::class, 'ping'])->middleware('throttle:10,1')->name('session.ping');
        Route::get('session/status', [SessionController::class, 'status'])->middleware('throttle:30,1')->name('session.status');

        Route::get('/', DashboardController::class)->name('dashboard');

        // Products
        Route::middleware('can:products.view')->group(function (): void {
            Route::get('products', [ProductController::class, 'index'])->name('products.index');
        });
        Route::middleware('can:products.create')->group(function (): void {
            Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('products', [ProductController::class, 'store'])->name('products.store');
        });
        Route::middleware('can:products.update')->group(function (): void {
            Route::get('products/{product:id}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('products/{product:id}', [ProductController::class, 'update'])->name('products.update');
            Route::patch('products/{product:id}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
        });
        Route::delete('products/{product:id}', [ProductController::class, 'destroy'])
            ->middleware('can:products.delete')->name('products.destroy');
        Route::patch('products/{product:id}/restore', [ProductController::class, 'restore'])
            ->middleware('can:products.delete')->withTrashed()->name('products.restore');

        // Categories
        Route::get('categories', [CategoryController::class, 'index'])->middleware('can:categories.view')->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->middleware('can:categories.create')->name('categories.store');
        Route::get('categories/{category:id}/edit', [CategoryController::class, 'edit'])->middleware('can:categories.update')->name('categories.edit');
        Route::put('categories/{category:id}', [CategoryController::class, 'update'])->middleware('can:categories.update')->name('categories.update');
        Route::delete('categories/{category:id}', [CategoryController::class, 'destroy'])->middleware('can:categories.delete')->name('categories.destroy');

        // Portfolio projects
        Route::get('projects', [ProjectController::class, 'index'])->middleware('can:projects.view')->name('projects.index');
        Route::get('projects/create', [ProjectController::class, 'create'])->middleware('can:projects.create')->name('projects.create');
        Route::post('projects', [ProjectController::class, 'store'])->middleware('can:projects.create')->name('projects.store');
        Route::get('projects/{project:id}/edit', [ProjectController::class, 'edit'])->middleware('can:projects.update')->name('projects.edit');
        Route::put('projects/{project:id}', [ProjectController::class, 'update'])->middleware('can:projects.update')->name('projects.update');
        Route::delete('projects/{project:id}', [ProjectController::class, 'destroy'])->middleware('can:projects.delete')->name('projects.destroy');

        // Messages from the contact form
        Route::get('messages', [MessageController::class, 'index'])->middleware('can:leads.view')->name('messages.index');
        Route::get('messages/{message}', [MessageController::class, 'show'])->middleware('can:leads.view')->name('messages.show');
        Route::patch('messages/read-all', [MessageController::class, 'readAll'])->middleware('can:leads.update')->name('messages.read-all');
        Route::patch('messages/{message}/read', [MessageController::class, 'read'])->middleware('can:leads.update')->name('messages.read');
        Route::patch('messages/{message}/unread', [MessageController::class, 'unread'])->middleware('can:leads.update')->name('messages.unread');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->middleware('can:leads.delete')->name('messages.destroy');
        Route::patch('messages/{message}/restore', [MessageController::class, 'restore'])->middleware('can:leads.delete')->withTrashed()->name('messages.restore');

        // Website pages (text)
        Route::get('pages', [PageController::class, 'index'])->middleware('can:content.view')->name('pages.index');
        Route::get('pages/{page:id}/edit', [PageController::class, 'edit'])->middleware('can:content.update')->name('pages.edit');
        Route::put('pages/{page:id}', [PageController::class, 'update'])->middleware('can:content.update')->name('pages.update');

        // Team (About page)
        Route::get('team', [TeamController::class, 'index'])->middleware('can:content.view')->name('team.index');
        Route::get('team/create', [TeamController::class, 'create'])->middleware('can:content.create')->name('team.create');
        Route::post('team', [TeamController::class, 'store'])->middleware('can:content.create')->name('team.store');
        Route::get('team/{member:id}/edit', [TeamController::class, 'edit'])->middleware('can:content.update')->name('team.edit');
        Route::put('team/{member:id}', [TeamController::class, 'update'])->middleware('can:content.update')->name('team.update');
        Route::delete('team/{member:id}', [TeamController::class, 'destroy'])->middleware('can:content.delete')->name('team.destroy');

        // News & updates
        Route::get('news', [PostController::class, 'index'])->middleware('can:content.view')->name('posts.index');
        Route::get('news/create', [PostController::class, 'create'])->middleware('can:content.create')->name('posts.create');
        Route::post('news', [PostController::class, 'store'])->middleware('can:content.create')->name('posts.store');
        Route::get('news/{post:id}/edit', [PostController::class, 'edit'])->middleware('can:content.update')->name('posts.edit');
        Route::put('news/{post:id}', [PostController::class, 'update'])->middleware('can:content.update')->name('posts.update');
        Route::delete('news/{post:id}', [PostController::class, 'destroy'])->middleware('can:content.delete')->name('posts.destroy');

        // Site settings
        Route::get('settings/{group?}', [SettingController::class, 'edit'])->middleware('can:settings.view')->name('settings.edit');
        Route::put('settings/{group}', [SettingController::class, 'update'])->middleware('can:settings.manage')->name('settings.update');

        // Staff accounts
        Route::get('users', [UserController::class, 'index'])->middleware('can:users.view')->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->middleware('can:users.create')->name('users.create');
        Route::post('users', [UserController::class, 'store'])->middleware('can:users.create')->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->middleware('can:users.update')->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('can:users.update')->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('can:users.delete')->name('users.destroy');

        // Own account
        Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('account', [AccountController::class, 'update'])->name('account.update');
    });
});
