<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\SubcategoryController;
use App\Http\Controllers\Api\V2\CartController;

Route::prefix('v1')->group(function () {

    Route::prefix('admin')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('dashboard', [DashboardController::class, 'index']);


            Route::apiResource('products', ProductController::class)->only(['index','show']);
            Route::apiResource('categories', CategoryController::class)->only(['index','show']);
            Route::apiResource('subcategories', SubcategoryController::class)->only(['index','show']);
            Route::middleware(['role:super_admin|product_manager'])->group(function () {
                Route::apiResource('products', ProductController::class)->except(['index','show']);
                Route::apiResource('categories', CategoryController::class)->except(['index','show']);
                Route::apiResource('subcategories', SubcategoryController::class)->except(['index','show']);
            });

            Route::middleware(['role:super_admin|user_manager'])->group(function () {
                Route::apiResource('users', UserController::class);
            });

        });


    });
});


// Route::post('test', [AuthController::class, 'register']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('v1/admin/products', ProductController::class)->only(['index','show']);
//     Route::apiResource('v1/admin/categories', CategoryController::class)->only(['index','show']);
//     Route::middleware('check.product.manager')->group(function () {
//         Route::apiResource('v1/admin/products', ProductController::class)->except(['index','show']);
//         Route::apiResource('v1/admin/categories', CategoryController::class)->except(['index','show']);
//     });

//     Route::middleware('check.user.manager')->group(function () {
//         Route::apiResource('v1/admin/users', UserController::class);
//     });
// });
Route::prefix('v2')->group(function () {
Route::post('/check-stock/{productId}/{quantity}', [CartController::class, 'checkStock']);
});
