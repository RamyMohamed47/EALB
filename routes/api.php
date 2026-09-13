<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
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
