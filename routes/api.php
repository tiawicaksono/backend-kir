<?php

use App\Http\Controllers\ApiIntegrationController;
use App\Http\Controllers\ApiTokenController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterAreaController;
use App\Http\Controllers\MasterBahanBakarController;
use App\Http\Controllers\MasterBahanUtamaController;
use App\Http\Controllers\MasterJenisKendaraanController;
use App\Http\Controllers\MasterKecamatanController;
use App\Http\Controllers\MasterKelasJalanController;
use App\Http\Controllers\MasterKelurahanController;
use App\Http\Controllers\MasterKotaController;
use App\Http\Controllers\MasterMerkController;
use App\Http\Controllers\MasterMerkVarianController;
use App\Http\Controllers\MasterMerkVarianTipeController;
use App\Http\Controllers\MasterPegawaiController;
use App\Http\Controllers\MasterProvinsiController;
use App\Http\Controllers\MasterStatusPenerbitanController;
use App\Http\Controllers\MasterSubJenisKendaraanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\UserManagementController;

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
    Route::get('/menus/me', [MenuController::class, 'me']); // udah g kepakek
    Route::get('/user', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-token', [AuthController::class, 'logoutToken']);

    /**
     * ===============================
     * Sinkron Kemenhub
     * ===============================
     */
    // Status Penerbitan
    Route::prefix('statuspenerbitan')->group(function () {
        Route::get('/', [MasterStatusPenerbitanController::class, 'index']);
        Route::post('/sync', [MasterStatusPenerbitanController::class, 'sync']);
    });
    // Kelas Jalan
    Route::prefix('kelasjalan')->group(function () {
        Route::get('/', [MasterKelasJalanController::class, 'index']);
        Route::post('/sync', [MasterKelasJalanController::class, 'sync']);
    });
    // Bahan Bakar
    Route::prefix('fuel')->group(function () {
        Route::get('/', [MasterBahanBakarController::class, 'index']);
        Route::post('/sync', [MasterBahanBakarController::class, 'sync']);
    });
    // Pegawai
    Route::prefix('pegawai')->group(function () {
        Route::get('/', [MasterPegawaiController::class, 'index']);
        Route::post('/sync', [MasterPegawaiController::class, 'sync']);
    });
    // Area
    Route::prefix('area')->group(function () {
        Route::get('/', [MasterAreaController::class, 'index']);
        Route::post('/sync', [MasterAreaController::class, 'sync']);
    });
    // Merk
    Route::prefix('merk')->group(function () {
        Route::post('/', [MasterMerkController::class, 'index']);
        Route::get('/{masterMerk}', [MasterMerkController::class, 'show']);
        Route::post('/sync', [MasterMerkController::class, 'sync']);
    });
    // Varian Merk
    Route::prefix('variantype')->group(function () {
        Route::post('/', [MasterMerkVarianController::class, 'index']);
        Route::get('/{masterMerkVarian}', [MasterMerkVarianController::class, 'show']);
        Route::post('/sync', [MasterMerkVarianController::class, 'sync']);
    });
    // Tipe Varian Merk
    Route::prefix('varian')->group(function () {
        Route::post('/', [MasterMerkVarianTipeController::class, 'index']);
        Route::get('/{masterMerkVarianTipe}', [MasterMerkVarianTipeController::class, 'show']);
        Route::post('/sync', [MasterMerkVarianTipeController::class, 'sync']);
    });
    // Jenis Kendaraan
    Route::prefix('vehicletype')->group(function () {
        Route::post('/', [MasterJenisKendaraanController::class, 'index']);
        Route::get('/{masterJenisKendaraan}', [MasterJenisKendaraanController::class, 'show']);
        Route::post('/sync', [MasterJenisKendaraanController::class, 'sync']);
    });
    // Sub Jenis Kendaraan
    Route::prefix('subvehicletype')->group(function () {
        Route::post('/', [MasterSubJenisKendaraanController::class, 'index']);
        Route::get('/{masterSubJenisKendaraan}', [MasterSubJenisKendaraanController::class, 'show']);
        Route::post('/sync', [MasterSubJenisKendaraanController::class, 'sync']);
    });

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

    Route::prefix('pengaturan')->group(function () {
        // API KEYS
        Route::get('/api-keys', [ApiTokenController::class, 'index']);
        Route::post('/api-keys/create', [ApiTokenController::class, 'store']);
        Route::put('/api-keys/update/{id}', [ApiTokenController::class, 'update']);
        Route::put('/api-keys/update-status', [ApiTokenController::class, 'updateStatus']);
        Route::delete('/api-keys/delete/{id}', [ApiTokenController::class, 'destroy']);

        // API INTEGRATIONS
        Route::get('/api-integrations', [ApiIntegrationController::class, 'index']);
        Route::get('/api-integrations/detail/{prefix}', [ApiIntegrationController::class, 'show']);
    });

    // Pengaturan
    // ❗ KHUSUS ADMIN
    Route::prefix('pengaturan')
        ->middleware('check.role:1')
        ->group(function () {
            // USER MANAGEMENT
            Route::get('/user-management/counts', [UserManagementController::class, 'counts']);
            Route::get('/user-management', [UserManagementController::class, 'index']);
            Route::post('/user-management/create', [UserManagementController::class, 'store']);
            Route::put('/user-management/update/{id}', [UserManagementController::class, 'updateProfile']);
            Route::delete('/user-management/delete/{id}', [UserManagementController::class, 'destroy']);
            Route::put('/user-management/update/{id}/roles', [UserManagementController::class, 'updateRole']);

            // ROLE MANAGEMENT
            Route::get('/role-management', [RoleManagementController::class, 'index']);
            Route::post('/role-management/create', [RoleManagementController::class, 'store']);
            Route::put('/role-management/update/{id}', [RoleManagementController::class, 'update']);
            Route::delete('/role-management/delete/{id}', [RoleManagementController::class, 'destroy']);

            // PROVINSI
            Route::get('/provinsi/counts', [MasterProvinsiController::class, 'counts']);
            Route::apiResource('provinsi', MasterProvinsiController::class);
            // KOTA/KABUPATEN
            Route::apiResource('kota', MasterKotaController::class);
            // KECAMATAN
            Route::apiResource('kecamatan', MasterKecamatanController::class);
            // KELURAHAN/DESA
            Route::apiResource('kelurahan', MasterKelurahanController::class);

            // BAHAN UTAMA
            Route::get('/bahanutama/counts', [MasterBahanUtamaController::class, 'counts']);
            Route::apiResource('bahanutama', MasterBahanUtamaController::class);

            // STATUS PENERBITAN
            Route::get('/statuspenerbitan/counts', [MasterStatusPenerbitanController::class, 'counts']);
            Route::apiResource('statuspenerbitan', MasterStatusPenerbitanController::class);
        });
});
