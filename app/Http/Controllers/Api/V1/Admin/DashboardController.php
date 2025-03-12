<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Notifications\LowStockNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {

        if (!auth()->user()->can('view_dashboard')) {
            return [
                'status' => 'error',
                'message' => 'Unauthorized'
            ];
        }


        // $lowStockCount = $lowStockProducts->count();
        // if ($lowStockCount > 0) {
        //     $admins = User::role('super_admin')->get();
        //     Notification::send($admins, new LowStockNotification($lowStockProducts));
        // }


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

            $admins = User::role('super_admin')->get();

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
