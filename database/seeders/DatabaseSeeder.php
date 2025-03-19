<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Category;
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
        User::factory(10)->create();
        Category::factory(10)->create();
        Subcategory::factory(10)->create();
        Product::factory(10)->create();
        CartItem::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
