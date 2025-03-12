<?php

use App\Http\Controllers\Api\OpeningHoursController;
use App\Http\Controllers\Api\StoreHoursController;
use App\Http\Controllers\Api\Admin\StoreHoursConfigController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('opening-hours')->group(function () {
    Route::get('/status/{date?}', [OpeningHoursController::class, 'status']);
    Route::get('/schedule/{date?}', [OpeningHoursController::class, 'openingHours']);
    Route::post('/check-date', [OpeningHoursController::class, 'checkDate']);
});

Route::prefix('store')->group(function () {
    Route::get('status', [StoreHoursController::class, 'status']);
    Route::get('hours/today', [StoreHoursController::class, 'today']);
    Route::get('hours/week', [StoreHoursController::class, 'week']);
});

// Public store hours endpoints
Route::prefix('store-hours')->group(function () {
    Route::get('status', [StoreHoursController::class, 'status']);
    Route::get('today', [StoreHoursController::class, 'today']);
    Route::get('week', [StoreHoursController::class, 'week']);
});

// Admin store hours configuration endpoints
Route::prefix('admin/store-hours')
    ->middleware(['auth:sanctum', 'web'])
    ->group(function () {
        Route::get('/', [StoreHoursConfigController::class, 'index']);
        Route::post('/', [StoreHoursConfigController::class, 'store']);
        Route::get('/{config}', [StoreHoursConfigController::class, 'show']);
        Route::put('/{config}', [StoreHoursConfigController::class, 'update']);
        Route::delete('/{config}', [StoreHoursConfigController::class, 'destroy']);
        Route::post('/bulk-update', [StoreHoursConfigController::class, 'bulkUpdate']);
    }); 