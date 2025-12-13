<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\HomeComponentController;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\HomeController;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| API endpoints for version 1
|
*/

Route::middleware('api')->group(function () {
    // Single endpoint cho trang chủ - giảm từ 9 requests xuống 1
    Route::get('/home', [HomeController::class, 'index'])->name('home.index');

    // Public settings
    Route::get('/settings', [SettingController::class, 'show'])->name('settings.show');

    // Public home components
    Route::get('/home-components', [HomeComponentController::class, 'index'])->name('home-components.index');
    Route::get('/home-components/{type}', [HomeComponentController::class, 'show'])->name('home-components.show');

    // Public menus
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');

    // Public auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.updateProfile');
            Route::post('/change-password', [AuthController::class, 'changePassword'])->name('auth.changePassword');
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        });

        // Admin only routes
        Route::middleware('admin')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::apiResource('posts', PostController::class);
            Route::apiResource('media', MediaController::class);
            Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        });
    });
});
