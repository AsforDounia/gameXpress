<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;

class ProductTest extends TestCase
{

    public function test_a_product_can_be_created_with_images()
    {
        // Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('product_manager');
        $this->actingAs($user);
        $subcategory = Subcategory::factory()->create();
        $data = [
            'name' => 'Test Product',
            'slug' => 'test-product-6',
            'price' => 199.99,
            'stock' => 10,
            'status' => "available",
            'subcategory_id' => $subcategory->id,
            'images' => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.jpg')
            ]
        ];

        $response = $this->postJson('/api/v1/admin/products', $data);

        $response->assertJsonStructure([
            'message',
            'product' => [
                'id', 'name', 'slug', 'price', 'stock', 'status', 'subcategory_id',
                'product_images' => [
                    '*' => ['image_url', 'is_primary']
                ]
            ]
        ]);

        $product = Product::first();
        foreach ($product->productImages as $image) {
            Storage::disk('public')->assertExists($image->image_path);
        }
    }
}
