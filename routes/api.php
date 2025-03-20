<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\SubcategoryController;
use App\Http\Controllers\Api\V2\UserRoleController;
use App\Http\Controllers\Api\V2\CartController;
use App\Http\Controllers\Api\V3\OrderController;
use App\Http\Controllers\Api\V3\PaymentController;

Route::prefix('v1')->group(function () {

    Route::prefix('admin')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('dashboard', [DashboardController::class, 'index']);


            Route::apiResource('products', ProductController::class)->only(['index', 'show']);
            Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
            Route::apiResource('subcategories', SubcategoryController::class)->only(['index', 'show']);
            Route::middleware(['role:super_admin|product_manager'])->group(function () {
                Route::get('dashboard', [DashboardController::class, 'index']);
                Route::apiResource('products', ProductController::class)->except(['index', 'show']);
                Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
                Route::apiResource('subcategories', SubcategoryController::class)->except(['index', 'show']);
            });

            Route::middleware(['role:super_admin|user_manager'])->group(function () {
                Route::apiResource('users', UserController::class);
            });
            // Route::middleware(['role:super_admin'])->group(function () {
            //     Route::apiResource('roles',UserRoleController::class);

            // });



        });
    });
});

Route::prefix('v1/admin')->group(function () {
    Route::middleware(['auth:sanctum'])->get('/merge', [CartController::class, 'cartMerge']);
});

Route::prefix('v2')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/AddToCart/{product_id}', [CartController::class, 'AddToCart']);
        Route::get('/getCart', [CartController::class, 'getCart']);

        Route::delete('/destroyProductForClient/{productId}', [CartController::class, 'destoryProductFromCart']);
        Route::post('/calculateTotalForClient', [CartController::class, 'calculateTotalofCart']);

        Route::middleware(['role:super_admin'])->group(function () {
            Route::apiResource('roles', UserRoleController::class);
            Route::post('/roles/updateRolePermitions/{roleId}', [UserRoleController::class, 'updateRolePermitions']);
        });
    });
    Route::post('/AddToCart/Guest/{product_id}', [CartController::class, 'AddToCart']);
    Route::get('/getCart/Guest', [CartController::class, 'getCart']);

    Route::post('/AddToCart', [CartController::class, 'AddToCart']);


    Route::post('/AddToCart/Guest', [CartController::class, 'AddToCartGuest']);
    Route::delete('/destroyProductForGuet/{productId}', [CartController::class, 'destoryProductFromCart']);
    Route::post('/calculateTotalForGuest', [CartController::class, 'calculateTotalofCart']);
});


Route::prefix('v3')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('orders', OrderController::class);
        Route::patch('orders/cancel/{order}', [OrderController::class, 'cancel'])->name('order.cancel');
    });
});

// Route::post('V2/addToCart', [CartController::class, 'addToCart']);

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
    Route::put('/updatequantity', [CartController::class, 'modifyQuantityProductInCartUser']);
});
Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook']);

Route::post('/checkout', [PaymentController::class, 'createCheckoutSession']);
