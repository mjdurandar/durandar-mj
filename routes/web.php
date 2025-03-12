<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\Admin\StoreHoursConfigController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('/store-hours', function () {
            return Inertia::render('Admin/StoreHours');
        })->name('admin.store-hours');

        // Admin API endpoints
        Route::prefix('api')->group(function () {
            Route::get('/store-hours', [StoreHoursConfigController::class, 'index']);
            Route::post('/store-hours/bulk-update', [StoreHoursConfigController::class, 'bulkUpdate']);
            Route::put('/store-hours/{config}', [StoreHoursConfigController::class, 'update']);
        });
    });
});

require __DIR__.'/auth.php';
