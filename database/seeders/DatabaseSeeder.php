<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Tests\Unit\SubCategoryTest;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->withRole()->count(10)->create();
        Category::factory(10)->create();
        Subcategory::factory(10)->create();
        Product::factory(10)->create();
        Order::factory()
            ->count(10)
            ->hasOrderItems(rand(1, 5))
            ->create();
    }
}
