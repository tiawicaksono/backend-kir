<?php

use App\Http\Controllers\ApiTokenController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterAreaController;
use App\Http\Controllers\MasterBahanBakarController;
use App\Http\Controllers\MasterJenisKendaraanController;
use App\Http\Controllers\MasterKelasJalanController;
use App\Http\Controllers\MasterMerkController;
use App\Http\Controllers\MasterMerkVarianController;
use App\Http\Controllers\MasterMerkVarianTipeController;
use App\Http\Controllers\MasterPegawaiController;
use App\Http\Controllers\MasterStatusPenerbitanController;
use App\Http\Controllers\MasterSubJenisKendaraanController;
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
    // Auth
    Route::get('/user', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-token', [AuthController::class, 'logoutToken']);

    // Sinkron Kemenhub
    Route::post('/status-penerbitan/sync', [MasterStatusPenerbitanController::class, 'sync']);
    Route::post('/kelas-jalan/sync', [MasterKelasJalanController::class, 'sync']);
    Route::post('/bahan-bakar/sync', [MasterBahanBakarController::class, 'sync']);
    Route::post('/merk/sync', [MasterMerkController::class, 'sync']);
    Route::post('/merk-varian/sync', [MasterMerkVarianController::class, 'sync']);
    Route::post('/merk-varian-tipe/sync', [MasterMerkVarianTipeController::class, 'sync']);
    Route::post('/jenis-kendaraan/sync', [MasterJenisKendaraanController::class, 'sync']);
    Route::post('/sub-jenis-kendaraan/sync', [MasterSubJenisKendaraanController::class, 'sync']);
    Route::post('/pegawai/sync', [MasterPegawaiController::class, 'sync']);
    Route::post('/area/sync', [MasterAreaController::class, 'sync']);

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
