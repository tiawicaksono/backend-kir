<?php

use App\Http\Controllers\ApiTokenController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-token', [AuthController::class, 'loginToken']);


// Route::middleware(['auth:sanctum', 'check.menu'])->get('/pengaturan/api-keys', [ApiTokenController::class, 'index']);

// Route::middleware(['auth:sanctum', 'check.menu'])->put('/pengaturan/api-keys/update-status', [ApiTokenController::class, 'updateStatus']);

// Route::middleware(['auth:sanctum', 'check.menu'])->delete('/pengaturan/api-keys/delete/{id}', [ApiTokenController::class, 'destroy']);
/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'check.menu'])->group(function () {
    Route::post('/logout-token', [AuthController::class, 'logoutToken']);
    // Auth
    Route::get('/user', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Menu
    Route::get('/menus/me', [MenuController::class, 'me']);
    // Loket
    Route::prefix('loket')->group(function () {
        Route::get('/pembayaran', fn() => response()->json(['message' => 'Pembayaran OK']));
        Route::post('/pembayaran', fn() => response()->json(['message' => 'Create Pembayaran OK']));
    });

    // Master
    Route::prefix('master')->group(function () {
        Route::get('/rolling-alat', fn() => response()->json(['message' => 'Rolling Alat OK']));
        Route::post('/rolling-alat', fn() => response()->json(['message' => 'Create Rolling Alat OK']));
        Route::put('/rolling-alat/{id}', fn() => response()->json(['message' => 'Update Rolling Alat OK']));
        Route::delete('/rolling-alat/{id}', fn() => response()->json(['message' => 'Delete Rolling Alat OK']));
    });

    // Pengaturan
    Route::prefix('pengaturan')->group(function () {
        Route::get('/api-keys', [ApiTokenController::class, 'index']);
        Route::post('/api-keys/create', [ApiTokenController::class, 'store']);
        Route::put('/api-keys/update/{id}', [ApiTokenController::class, 'update']);
        Route::put('/api-keys/update-status', [ApiTokenController::class, 'updateStatus']);
        Route::delete('/api-keys/delete/{id}', [ApiTokenController::class, 'destroy']);
        Route::post('/api-integrations', fn() => response()->json(['message' => 'Create Pembayaran OK']));
    });
});
