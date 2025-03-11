<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\UserController;

Route::prefix('v1')->group(function () {
    Route::prefix('admin')->group(function () {
       
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        // Protected routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('dashboard', [DashboardController::class, 'index']);

            // Product routes
            Route::apiResource('products', ProductController::class);

            // Category routes
            Route::apiResource('categories', CategoryController::class);

            // User routes
            Route::apiResource('users', UserController::class);
        });
    });
});
