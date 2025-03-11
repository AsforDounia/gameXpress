<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Notifications\LowStockNotification;
use Illuminate\Http\Request;
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


        $lowStockProducts = Product::where('stock', '<=', 5)->get();
        $lowStockCount = $lowStockProducts->count();

        if ($lowStockCount > 0) {
            $admins = User::role('super_admin')->get();
            Notification::send($admins, new LowStockNotification($lowStockProducts));
        }

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
}
