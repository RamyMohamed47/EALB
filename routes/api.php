<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('login');
Route::post('/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('admin')->group(function (): void {
        Route::apiResource('users', UserController::class);
        Route::get(
            '/users/{user}/products',
            [ProductController::class, 'userProducts']
        )->name('users.products.index');
    });

    Route::apiResource('products', ProductController::class);
});
