<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Notifications\LowStockNotification;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/dashboard",
     *     summary="Get dashboard statistics",
     *     tags={"Dashboard"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_products", type="integer", example=100),
     *                 @OA\Property(property="total_users", type="integer", example=50),
     *                 @OA\Property(property="total_categories", type="integer", example=10),
     *                 @OA\Property(property="total_subcategories", type="integer", example=20),
     *                 @OA\Property(
     *                     property="low_stock_products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Product A"),
     *                         @OA\Property(property="stock", type="integer", example=3)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index()
    {

        $this->checkLowStock();
        $lowStockProducts = Product::where('stock', '<=', 5)->get();
        $dashboardData = [
            'total_products' => Product::count(),
            'total_users' => User::count(),
            'total_categories' => Category::count(),
            'total_subcategories' => Subcategory::count(),
            'low_stock_products' => $lowStockProducts->map(fn($product) => [
                'name' => $product->name,
                'stock' => $product->stock,
            ]),
        ];

        return [
            'status' => 'success',
            'data' => $dashboardData,
        ];
    }

    public function checkLowStock()
    {
        $lowStockProducts = Product::where('stock', '<=', 5)->get();
        $previousLowStockIds = Cache::get('low_stock_products', []);
        $newLowStockProducts = $lowStockProducts->whereNotIn('id', $previousLowStockIds);

        if ($newLowStockProducts->isNotEmpty()) {

            $users = User::all();
            $admins = $users->filter(function ($user) {
                return $user->hasRole('super_admin');
            });
            $sortedProducts = $newLowStockProducts->merge(
                $lowStockProducts->whereIn('id', $previousLowStockIds)
            );

            foreach ($admins as $admin) {
                $admin->notify(new LowStockNotification($sortedProducts));
            }

            Cache::put('low_stock_products', $lowStockProducts->pluck('id')->toArray(), now()->addHours(24));
        }

    }
}
