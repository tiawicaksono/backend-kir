<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-token', [AuthController::class, 'loginToken']);


/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Menu
    |--------------------------------------------------------------------------
    */

    Route::get('/menus/me', [MenuController::class, 'me']);

    /*
    |--------------------------------------------------------------------------
    | Application Routes (Auto Protected)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['check.menu'])->group(function () {

        /*
        | LOKET
        */
        Route::prefix('loket')->group(function () {

            Route::get(
                '/pembayaran',
                fn() =>
                response()->json(['message' => 'Pembayaran OK'])
            );

            Route::post(
                '/pembayaran',
                fn() =>
                response()->json(['message' => 'Create Pembayaran OK'])
            );
        });

        /*
        | MASTER
        */
        Route::prefix('master')->group(function () {

            Route::get(
                '/rolling-alat',
                fn() =>
                response()->json(['message' => 'Rolling Alat OK'])
            );

            Route::post(
                '/rolling-alat',
                fn() =>
                response()->json(['message' => 'Create Rolling Alat OK'])
            );

            Route::put(
                '/rolling-alat/{id}',
                fn() =>
                response()->json(['message' => 'Update Rolling Alat OK'])
            );

            Route::delete(
                '/rolling-alat/{id}',
                fn() =>
                response()->json(['message' => 'Delete Rolling Alat OK'])
            );
        });
    });
});
