<?php

use App\Http\Controllers\Api\StoreHoursController;
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

// Public store hours endpoints
Route::prefix('store-hours')->group(function () {
    Route::get('status', [StoreHoursController::class, 'getCurrentStatus']);
    Route::get('today', [StoreHoursController::class, 'getCurrentStatus']); // Alias for status
    Route::get('check-date/{date}', [StoreHoursController::class, 'checkDateStatus']);
    Route::get('schedule', [StoreHoursController::class, 'getWeeklySchedule']);
    Route::get('week', [StoreHoursController::class, 'getWeeklySchedule']); // Alias for schedule
}); 