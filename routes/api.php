<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('test', [AuthController::class, 'register']);

// Route::apiResource('v1/admin/products', ProductController::class)->middleware('check.product.manager');


Route::prefix('v1')->group(function () {

    Route::prefix('admin')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('dashboard', [DashboardController::class, 'index']);
        });
    });
});




Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('v1/admin/products', ProductController::class)->only(['index','show']);
    Route::apiResource('v1/admin/categories', CategoryController::class)->only(['index','show']);
    Route::middleware('check.product.manager')->group(function () {
        Route::apiResource('v1/admin/products', ProductController::class)->except(['index','show']);
        Route::apiResource('v1/admin/categories', CategoryController::class)->except(['index','show']);
    });
});
