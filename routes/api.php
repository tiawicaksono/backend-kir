<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {

    // test route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // logout route
    Route::post('/logout', [AuthController::class, 'logout']);

    // menu untuk user yang login
    Route::get('/menus/me', [MenuController::class, 'me']);

    Route::middleware('menu.access')->group(function () {
        Route::get('/loket/pembayaran', fn() => response()->json(['ok']));
        Route::get('/master/rolling-alat', fn() => response()->json(['ok']));
    });
});
